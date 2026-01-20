<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Services\PesapalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PesapalPaymentController extends Controller
{
    public function __construct(
        private PesapalService $pesapalService
    ) {
        $this->middleware('auth:sanctum')->except(['callback', 'ipn']);
    }

    /**
     * Initiate a payment with Pesapal
     */
    public function initiatePayment(PaymentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $merchantReference = $this->pesapalService->generateMerchantReference();

            // Prepare order data for Pesapal
            $orderData = [
                'merchant_reference' => $merchantReference,
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency', config('services.pesapal.default_currency')),
                'description' => $request->input('description'),
                'callback_url' => config('services.pesapal.callback_url'),
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'customer_first_name' => explode(' ', $user->name)[0],
                'customer_last_name' => explode(' ', $user->name)[1] ?? '',
                'country_code' => $request->input('country_code', 'KE'),
                'address_line_1' => $request->input('address_line_1'),
                'address_line_2' => $request->input('address_line_2'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postal_code' => $request->input('postal_code'),
            ];

            // Submit order to Pesapal
            $pesapalResponse = $this->pesapalService->submitOrderRequest($orderData);

            if (!$pesapalResponse['success']) {
                throw new \Exception($pesapalResponse['error']);
            }

            // Create payment record
            $paymentData = [
                'user_id' => $user->id,
                'merchant_reference' => $merchantReference,
                'order_tracking_id' => $pesapalResponse['order_tracking_id'],
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency', 'KES'),
                'description' => $request->input('description'),
                'payment_type' => $request->input('payment_type'),
                'payable_type' => $request->input('payable_type'),
                'payable_id' => $request->input('payable_id'),
                'recipient_id' => $request->input('recipient_id'),
                'gateway_response' => $pesapalResponse
            ];

            $payment = $this->pesapalService->createPaymentRecord($paymentData);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'order_tracking_id' => $pesapalResponse['order_tracking_id'],
                'redirect_url' => $pesapalResponse['redirect_url'],
                'merchant_reference' => $merchantReference,
                'message' => 'Payment initiated successfully. Please complete payment on Pesapal.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment initiation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment initiation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Pesapal callback
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $orderTrackingId = $request->input('OrderTrackingId');
            $merchantReference = $request->input('OrderMerchantReference');

            if (!$orderTrackingId) {
                throw new \Exception('Missing order tracking ID');
            }

            // Find payment record
            $payment = Payment::where('transaction_id', $merchantReference)
                             ->orWhere('gateway_transaction_id', $orderTrackingId)
                             ->first();

            if (!$payment) {
                throw new \Exception('Payment record not found');
            }

            // Get transaction status from Pesapal
            $statusResponse = $this->pesapalService->getTransactionStatus($orderTrackingId);

            if (!$statusResponse['success']) {
                throw new \Exception($statusResponse['error']);
            }

            // Update payment status
            $updatedPayment = $this->pesapalService->updatePaymentStatus($payment, $statusResponse);

            Log::info('Payment callback processed', [
                'payment_id' => $payment->id,
                'order_tracking_id' => $orderTrackingId,
                'status' => $updatedPayment->status
            ]);

            // Redirect to frontend with payment status
            $frontendUrl = config('app.frontend_url', config('app.url'));
            $redirectUrl = "{$frontendUrl}/payment/result?status={$updatedPayment->status}&payment_id={$payment->id}";

            return response()->json([
                'success' => true,
                'payment' => $updatedPayment,
                'redirect_url' => $redirectUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Payment callback failed', [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            $frontendUrl = config('app.frontend_url', config('app.url'));
            $redirectUrl = "{$frontendUrl}/payment/result?status=failed&error=" . urlencode($e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'redirect_url' => $redirectUrl
            ], 500);
        }
    }

    /**
     * Handle Pesapal IPN (Instant Payment Notification)
     */
    public function ipn(Request $request): JsonResponse
    {
        try {
            $orderTrackingId = $request->input('OrderTrackingId');
            $orderNotificationType = $request->input('OrderNotificationType');

            if (!$orderTrackingId) {
                return response()->json(['message' => 'Missing order tracking ID'], 400);
            }

            // Find payment record
            $payment = Payment::where('gateway_transaction_id', $orderTrackingId)->first();

            if (!$payment) {
                Log::warning('IPN received for unknown payment', [
                    'order_tracking_id' => $orderTrackingId
                ]);
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Get transaction status from Pesapal
            $statusResponse = $this->pesapalService->getTransactionStatus($orderTrackingId);

            if ($statusResponse['success']) {
                // Update payment status
                $this->pesapalService->updatePaymentStatus($payment, $statusResponse);

                Log::info('Payment IPN processed', [
                    'payment_id' => $payment->id,
                    'order_tracking_id' => $orderTrackingId,
                    'notification_type' => $orderNotificationType
                ]);
            }

            return response()->json(['message' => 'IPN processed successfully']);

        } catch (\Exception $e) {
            Log::error('Payment IPN processing failed', [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'IPN processing failed'], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Payment $payment): JsonResponse
    {
        try {
            // Check if user can access this payment
            if (!$this->canAccessPayment($payment)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            // Get latest status from Pesapal if payment is still pending
            if ($payment->status === 'Pending' && $payment->gateway_transaction_id) {
                $statusResponse = $this->pesapalService->getTransactionStatus($payment->gateway_transaction_id);
                
                if ($statusResponse['success']) {
                    $payment = $this->pesapalService->updatePaymentStatus($payment, $statusResponse);
                }
            }

            return response()->json([
                'success' => true,
                'payment' => $payment->load(['paymentGateway', 'paymentType', 'payable'])
            ]);

        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Status check failed'
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Payment $payment, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:500'
        ]);

        try {
            // Check if user can process refunds
            if (!$this->canProcessRefund($payment)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            // Check if payment can be refunded
            if ($payment->status !== 'Completed') {
                return response()->json([
                    'error' => 'Only completed payments can be refunded'
                ], 400);
            }

            $refundAmount = $request->input('amount');
            $confirmationCode = $payment->gateway_response['confirmation_code'] ?? null;

            if (!$confirmationCode) {
                return response()->json([
                    'error' => 'No confirmation code found for this payment'
                ], 400);
            }

            // Process refund with Pesapal
            $refundResponse = $this->pesapalService->processRefund(
                $confirmationCode,
                $refundAmount,
                Auth::user()->email
            );

            if (!$refundResponse['success']) {
                throw new \Exception($refundResponse['error']);
            }

            // Update payment status
            $payment->update([
                'status' => $refundAmount >= $payment->amount ? 'Refunded' : 'Partially Refunded',
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'refund' => $refundResponse
                ])
            ]);

            Log::info('Payment refund processed', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'refund_id' => $refundResponse['refund_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'refund_id' => $refundResponse['refund_id'],
                'payment' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Payment refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Refund processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for authenticated user
     */
    public function getPaymentHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);

            $payments = Payment::where('user_id', $user->id)
                              ->with(['paymentGateway', 'paymentType', 'payable'])
                              ->orderBy('created_at', 'desc')
                              ->paginate($perPage);

            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);

        } catch (\Exception $e) {
            Log::error('Payment history retrieval failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payment history'
            ], 500);
        }
    }

    /**
     * Check if user can access payment
     */
    private function canAccessPayment(Payment $payment): bool
    {
        $user = Auth::user();

        // Admin can access all payments
        if ($user->role === 'Admin') {
            return true;
        }

        // User can access their own payments
        if ($payment->user_id === $user->id) {
            return true;
        }

        // Recipients can access payments made to them
        if ($payment->recipient_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can process refunds
     */
    private function canProcessRefund(Payment $payment): bool
    {
        $user = Auth::user();

        // Only admins can process refunds
        return $user->role === 'Admin';
    }
}