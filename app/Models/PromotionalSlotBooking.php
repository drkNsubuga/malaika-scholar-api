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

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
class PromotionalSlotBooking extends Model
{
    protected $fillable = [
        'promotional_purchase_id',
        'homepage_promotional_slot_id',
        'promoted_opportunity_id',
        'booking_date',
        'is_active',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function promotionalPurchase(): BelongsTo
    {
        return $this->belongsTo(PromotionalPurchase::class);
    }

    public function homepagePromotionalSlot(): BelongsTo
    {
        return $this->belongsTo(HomepagePromotionalSlot::class);
    }

    public function promotedOpportunity(): BelongsTo
    {
        return $this->belongsTo(PromotedOpportunity::class);
    }
}