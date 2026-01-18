<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
class PromotedOpportunity extends Model
{
    protected $fillable = [
        'promotional_purchase_id',
        'opportunity_id',
        'promotion_type',
        'placement_priority',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function promotionalPurchase(): BelongsTo
    {
        return $this->belongsTo(PromotionalPurchase::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function promotionalAnalytics(): HasMany
    {
        return $this->hasMany(PromotionalAnalytic::class);
    }

    public function promotionalClicks(): HasMany
    {
        return $this->hasMany(PromotionalClick::class);
    }

    public function promotionalConversions(): HasMany
    {
        return $this->hasMany(PromotionalConversion::class);
    }

    // Helper method to check if promotion is currently active
    public function getIsCurrentlyActiveAttribute(): bool
    {
        return $this->is_active && 
               $this->starts_at <= now() && 
               $this->expires_at >= now();
    }
}