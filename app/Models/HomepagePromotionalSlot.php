<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Model;
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
class HomepagePromotionalSlot extends Model
{
    protected $fillable = [
        'slot_name',
        'slot_type',
        'max_items',
        'price_per_day',
        'dimensions',
        'is_active',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'dimensions' => 'array',
        'is_active' => 'boolean',
    ];

    public function promotionalSlotBookings(): HasMany
    {
        return $this->hasMany(PromotionalSlotBooking::class);
    }
}