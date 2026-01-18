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
class PromotionalConversion extends Model
{
    protected $fillable = [
        'promoted_opportunity_id',
        'application_id',
        'conversion_value',
        'attributed_at',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'attributed_at' => 'datetime',
    ];

    public function promotedOpportunity(): BelongsTo
    {
        return $this->belongsTo(PromotedOpportunity::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}