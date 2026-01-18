<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]
class City extends Model
{
    protected $fillable = [
        'state_id',
        'name',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}