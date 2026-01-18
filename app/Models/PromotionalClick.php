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
class PromotionalClick extends Model
{
    protected $fillable = [
        'promoted_opportunity_id',
        'user_id',
        'click_source',
        'clicked_at',
        'user_ip',
        'user_agent',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function promotedOpportunity(): BelongsTo
    {
        return $this->belongsTo(PromotedOpportunity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}