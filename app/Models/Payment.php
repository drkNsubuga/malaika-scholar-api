<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put()
    ]
)]
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
}