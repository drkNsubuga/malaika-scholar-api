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
class School extends Model
{
    protected $fillable = [
        'name',
        'city_id',
        'address',
        'contact_email',
        'contact_phone',
        'website',
        'school_type',
        'accreditation_info',
        'user_id',
        'is_verified',
    ];

    protected $casts = [
        'accreditation_info' => 'array',
        'is_verified' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}