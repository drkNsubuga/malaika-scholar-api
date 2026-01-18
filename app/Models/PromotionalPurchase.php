<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put()
    ]
)]
class PromotionalPurchase extends Model
{
    protected $fillable = [
        'purchaser_id',
        'promotional_package_id',
        'status',
        'purchased_at',
        'starts_at',
        'expires_at',
        'payment_id',
        'total_amount',
        'currency_code',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }

    public function promotionalPackage(): BelongsTo
    {
        return $this->belongsTo(PromotionalPackage::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function promotedOpportunities(): HasMany
    {
        return $this->hasMany(PromotedOpportunity::class);
    }

    public function promotionalSlotBookings(): HasMany
    {
        return $this->hasMany(PromotionalSlotBooking::class);
    }

    // Helper method to check if promotion is active
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'Active' && 
               $this->starts_at <= now() && 
               $this->expires_at >= now();
    }
}