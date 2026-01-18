<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]
class PromotionalAnalytic extends Model
{
    protected $fillable = [
        'promoted_opportunity_id',
        'date',
        'impressions_count',
        'clicks_count',
        'applications_count',
        'conversion_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'conversion_rate' => 'decimal:2',
    ];

    public function promotedOpportunity(): BelongsTo
    {
        return $this->belongsTo(PromotedOpportunity::class);
    }

    // Helper method to calculate click-through rate
    public function getClickThroughRateAttribute(): float
    {
        if ($this->impressions_count === 0) {
            return 0;
        }
        
        return ($this->clicks_count / $this->impressions_count) * 100;
    }

    // Helper method to calculate conversion rate
    public function getCalculatedConversionRateAttribute(): float
    {
        if ($this->clicks_count === 0) {
            return 0;
        }
        
        return ($this->applications_count / $this->clicks_count) * 100;
    }
}