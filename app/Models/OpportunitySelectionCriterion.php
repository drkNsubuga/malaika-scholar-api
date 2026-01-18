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
class OpportunitySelectionCriterion extends Model
{
    protected $fillable = [
        'opportunity_id',
        'criteria_name',
        'criteria_description',
        'weight_percentage',
        'sort_order',
    ];

    protected $casts = [
        'weight_percentage' => 'decimal:2',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}