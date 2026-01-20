<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PesapalService
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;
    private string $environment;
    private ?string $accessToken = null;
    private ?Carbon $tokenExpiry = null;

    public function __construct()
    {
        $this->environment = config('services.pesapal.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'live' 
            ? 'https://pay.pesapal.com/v3/api' 
            : 'https://cybqa.pesapal.com/pesapalv3/api';
        
        $this->consumerKey = config('services.pesapal.consumer_key');
        $this->consumerSecret = config('services.pesapal.consumer_secret');
    }

    /**
     * Authenticate with Pesapal and get access token
     */
    public function authenticate(): array
    {
        try {
            if ($this->isTokenValid()) {
                return [
                    'success' => true,
                    'token' => $this->accessToken
                ];
            }

            $response = Http::post("{$this->baseUrl}/Auth/RequestToken", [
                'consumer_key' => $this->consumerKey,
                'consumer_secret' => $this->consumerSecret
            ]);

            if (!$response->successful()) {
                throw new \Exception('Pesapal authentication failed: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['token'])) {
                throw new \Exception('No token received from Pesapal');
            }

            $this->accessToken = $data['token'];
            $this->tokenExpiry = now()->addSeconds($data['expiryDate'] ?? 3600);

            Log::info('Pesapal authentication successful', [
                'expires_at' => $this->tokenExpiry
            ]);

            return [
                'success' => true,
                'token' => $this->accessToken,
                'expires_at' => $this->tokenExpiry
            ];

        } catch (\Exception $e) {
            Log::error('Pesapal authentication failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Submit order request to Pesapal
     */
    public function submitOrderRequest(array $orderData): array
    {
        try {
            $authResult = $this->authenticate();
            if (!$authResult['success']) {
                return $authResult;
            }

            // Validate required order data
            $this->validateOrderData($orderData);

            // Prepare Pesapal order request
            $pesapalOrder = [
                'id' => $orderData['merchant_reference'],
                'currency' => $orderData['currency'] ?? 'KES',
                'amount' => $orderData['amount'],
                'description' => $orderData['description'],
                'callback_url' => $orderData['callback_url'],
                'notification_id' => $orderData['notification_id'] ?? $this->getDefaultNotificationId(),
                'billing_address' => [
                    'email_address' => $orderData['customer_email'],
                    'phone_number' => $orderData['customer_phone'] ?? null,
                    'country_code' => $orderData['country_code'] ?? 'KE',
                    'first_name' => $orderData['customer_first_name'],
                    'last_name' => $orderData['customer_last_name'],
                    'line_1' => $orderData['address_line_1'] ?? null,
                    'line_2' => $orderData['address_line_2'] ?? null,
                    'city' => $orderData['city'] ?? null,
                    'state' => $orderData['state'] ?? null,
                    'postal_code' => $orderData['postal_code'] ?? null,
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/Transactions/SubmitOrderRequest", $pesapalOrder);

            if (!$response->successful()) {
                throw new \Exception('Pesapal order submission failed: ' . $response->body());
            }

            $responseData = $response->json();

            if (!isset($responseData['order_tracking_id'])) {
                throw new \Exception('No order tracking ID received from Pesapal');
            }

            Log::info('Pesapal order submitted successfully', [
                'merchant_reference' => $orderData['merchant_reference'],
                'order_tracking_id' => $responseData['order_tracking_id']
            ]);

            return [
                'success' => true,
                'order_tracking_id' => $responseData['order_tracking_id'],
                'merchant_reference' => $responseData['merchant_reference'],
                'redirect_url' => $responseData['redirect_url'] ?? null,
                'error' => $responseData['error'] ?? null,
                'status' => $responseData['status'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Pesapal order submission failed', [
                'merchant_reference' => $orderData['merchant_reference'] ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get transaction status from Pesapal
     */
    public function getTransactionStatus(string $orderTrackingId): array
    {
        try {
            $authResult = $this->authenticate();
            if (!$authResult['success']) {
                return $authResult;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json'
            ])->get("{$this->baseUrl}/Transactions/GetTransactionStatus", [
                'orderTrackingId' => $orderTrackingId
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get transaction status: ' . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'payment_method' => $data['payment_method'] ?? null,
                'amount' => $data['amount'] ?? null,
                'created_date' => $data['created_date'] ?? null,
                'confirmation_code' => $data['confirmation_code'] ?? null,
                'payment_status_description' => $data['payment_status_description'] ?? null,
                'description' => $data['description'] ?? null,
                'message' => $data['message'] ?? null,
                'payment_account' => $data['payment_account'] ?? null,
                'call_back_url' => $data['call_back_url'] ?? null,
                'status_code' => $data['status_code'] ?? null,
                'merchant_reference' => $data['merchant_reference'] ?? null,
                'payment_status_code' => $data['payment_status_code'] ?? null,
                'currency' => $data['currency'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Pesapal transaction status', [
                'order_tracking_id' => $orderTrackingId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process refund request
     */
    public function processRefund(string $confirmationCode, float $amount, string $username): array
    {
        try {
            $authResult = $this->authenticate();
            if (!$authResult['success']) {
                return $authResult;
            }

            $refundData = [
                'confirmation_code' => $confirmationCode,
                'amount' => $amount,
                'username' => $username
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/Transactions/RefundRequest", $refundData);

            if (!$response->successful()) {
                throw new \Exception('Pesapal refund request failed: ' . $response->body());
            }

            $data = $response->json();

            Log::info('Pesapal refund processed', [
                'confirmation_code' => $confirmationCode,
                'amount' => $amount,
                'refund_id' => $data['refund_id'] ?? null
            ]);

            return [
                'success' => true,
                'refund_id' => $data['refund_id'] ?? null,
                'status' => $data['status'] ?? null,
                'message' => $data['message'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Pesapal refund failed', [
                'confirmation_code' => $confirmationCode,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Register IPN (Instant Payment Notification) URL
     */
    public function registerIpnUrl(string $url, string $ipnNotificationType = 'GET'): array
    {
        try {
            $authResult = $this->authenticate();
            if (!$authResult['success']) {
                return $authResult;
            }

            $ipnData = [
                'url' => $url,
                'ipn_notification_type' => $ipnNotificationType
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/URLSetup/RegisterIPN", $ipnData);

            if (!$response->successful()) {
                throw new \Exception('Pesapal IPN registration failed: ' . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'ipn_id' => $data['ipn_id'] ?? null,
                'url' => $data['url'] ?? null,
                'created_date' => $data['created_date'] ?? null,
                'ipn_notification_type' => $data['ipn_notification_type'] ?? null,
                'ipn_status' => $data['ipn_status'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Pesapal IPN registration failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a payment record in the database
     */
    public function createPaymentRecord(array $paymentData): Payment
    {
        // Get or create Pesapal payment gateway
        $paymentGateway = PaymentGateway::firstOrCreate(
            ['name' => 'Pesapal'],
            [
                'is_active' => true,
                'configuration' => json_encode([
                    'supports_cards' => true,
                    'supports_mobile_money' => true,
                    'supports_bank_transfer' => true
                ])
            ]
        );

        // Get payment type
        $paymentType = PaymentType::where('name', $paymentData['payment_type'])->first();

        return Payment::create([
            'user_id' => $paymentData['user_id'],
            'payment_gateway_id' => $paymentGateway->id,
            'payment_type_id' => $paymentType?->id,
            'transaction_id' => $paymentData['merchant_reference'],
            'gateway_transaction_id' => $paymentData['order_tracking_id'] ?? null,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'KES',
            'status' => 'Pending',
            'payable_type' => $paymentData['payable_type'] ?? null,
            'payable_id' => $paymentData['payable_id'] ?? null,
            'recipient_id' => $paymentData['recipient_id'] ?? null,
            'description' => $paymentData['description'],
            'gateway_response' => $paymentData['gateway_response'] ?? null
        ]);
    }

    /**
     * Update payment status based on Pesapal response
     */
    public function updatePaymentStatus(Payment $payment, array $statusData): Payment
    {
        $status = $this->mapPesapalStatus($statusData['payment_status_code'] ?? null);
        
        $payment->update([
            'status' => $status,
            'gateway_transaction_id' => $statusData['confirmation_code'] ?? $payment->gateway_transaction_id,
            'gateway_response' => array_merge($payment->gateway_response ?? [], $statusData),
            'processed_at' => $status === 'Completed' ? now() : null
        ]);

        return $payment->fresh();
    }

    /**
     * Validate order data before sending to Pesapal
     */
    private function validateOrderData(array $orderData): void
    {
        $required = [
            'merchant_reference',
            'amount',
            'description',
            'callback_url',
            'customer_email',
            'customer_first_name',
            'customer_last_name'
        ];

        foreach ($required as $field) {
            if (!isset($orderData[$field]) || empty($orderData[$field])) {
                throw new \InvalidArgumentException("Required field '{$field}' is missing");
            }
        }

        if (!is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new \InvalidArgumentException('Amount must be a positive number');
        }

        if (!filter_var($orderData['customer_email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid customer email address');
        }
    }

    /**
     * Map Pesapal status codes to our payment statuses
     */
    private function mapPesapalStatus(?string $pesapalStatusCode): string
    {
        return match ($pesapalStatusCode) {
            '1' => 'Completed',
            '2' => 'Failed',
            '3' => 'Cancelled',
            default => 'Pending'
        };
    }

    /**
     * Check if current token is still valid
     */
    private function isTokenValid(): bool
    {
        return $this->accessToken && 
               $this->tokenExpiry && 
               $this->tokenExpiry->isFuture();
    }

    /**
     * Get default notification ID (should be configured in admin)
     */
    private function getDefaultNotificationId(): string
    {
        return config('services.pesapal.default_notification_id', '');
    }

    /**
     * Generate unique merchant reference
     */
    public function generateMerchantReference(string $prefix = 'MALAIKA'): string
    {
        return $prefix . '_' . time() . '_' . Str::random(8);
    }
}