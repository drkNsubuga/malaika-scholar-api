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
class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preference_key',
        'preference_value',
    ];

    protected $casts = [
        'preference_value' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}