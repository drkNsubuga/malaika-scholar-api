<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Laravel\Eloquent\Filter\ExactFilter;
use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Laravel\Eloquent\Filter\RangeFilter;
use ApiPlatform\Metadata\QueryParameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and (object.user == user or object.recipient == user or is_granted('ROLE_ADMIN'))"
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')"
        )
    ],
    middleware: ['auth:sanctum'],
    paginationItemsPerPage: 20
)]
#[QueryParameter(key: 'user_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'status', filter: ExactFilter::class)]
#[QueryParameter(key: 'payment_gateway_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'payment_type_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'amount', filter: RangeFilter::class)]
#[QueryParameter(key: 'order[created_at]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[amount]', filter: OrderFilter::class)]
class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'payment_gateway_id',
        'payment_type_id',
        'transaction_id',
        'gateway_transaction_id',
        'amount',
        'currency',
        'status',
        'payment_gateway',
        'payment_type',
        'recipient_id',
        'payable_type',
        'payable_id',
        'gateway_response',
        'description',
        'processed_at',
        'receipt_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_amount',
        'status_badge',
        'payment_method',
    ];

    protected $hidden = [
        'gateway_response' // Hide sensitive gateway data from API responses
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        $currencySymbols = [
            'KES' => 'KSh',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        ];

        $symbol = $currencySymbols[$this->currency] ?? $this->currency;
        return $symbol . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge for UI display
     */
    public function getStatusBadgeAttribute(): array
    {
        $badges = [
            'Pending' => ['color' => 'warning', 'text' => 'Pending'],
            'Completed' => ['color' => 'success', 'text' => 'Completed'],
            'Failed' => ['color' => 'danger', 'text' => 'Failed'],
            'Refunded' => ['color' => 'info', 'text' => 'Refunded'],
            'Partially Refunded' => ['color' => 'info', 'text' => 'Partially Refunded'],
            'Cancelled' => ['color' => 'secondary', 'text' => 'Cancelled']
        ];

        return $badges[$this->status] ?? ['color' => 'secondary', 'text' => $this->status];
    }

    /**
     * Get payment method from gateway response
     */
    public function getPaymentMethodAttribute(): ?string
    {
        if (!$this->gateway_response || !is_array($this->gateway_response)) {
            return null;
        }

        return $this->gateway_response['payment_method'] ?? 
               $this->gateway_response['payment_account'] ?? 
               null;
    }

    /**
     * Get Pesapal confirmation code
     */
    public function getConfirmationCodeAttribute(): ?string
    {
        if (!$this->gateway_response || !is_array($this->gateway_response)) {
            return null;
        }

        return $this->gateway_response['confirmation_code'] ?? null;
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'Completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['Failed', 'Cancelled']);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'Completed' && 
               $this->confirmation_code && 
               $this->created_at->diffInDays(now()) <= 365; // 1 year refund window
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'Completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['Failed', 'Cancelled']);
    }

    /**
     * Scope for payments by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for payments to recipient
     */
    public function scopeToRecipient($query, $recipientId)
    {
        return $query->where('recipient_id', $recipientId);
    }

    /**
     * Scope for payments by type
     */
    public function scopeByType($query, $paymentType)
    {
        return $query->where('payment_type', $paymentType);
    }

    /**
     * Scope for payments in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}