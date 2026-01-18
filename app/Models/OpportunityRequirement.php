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
class OpportunityRequirement extends Model
{
    protected $fillable = [
        'opportunity_id',
        'requirement_type',
        'requirement_description',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}