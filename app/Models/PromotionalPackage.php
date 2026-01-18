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
class PromotionalPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'package_type',
        'duration_days',
        'price',
        'currency_code',
        'features',
        'max_opportunities',
        'placement_priority',
        'includes_analytics',
        'includes_logo_display',
        'includes_homepage_feature',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'includes_analytics' => 'boolean',
        'includes_logo_display' => 'boolean',
        'includes_homepage_feature' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function promotionalPurchases(): HasMany
    {
        return $this->hasMany(PromotionalPurchase::class);
    }
}