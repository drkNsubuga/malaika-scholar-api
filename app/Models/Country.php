<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]
class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'currency_code',
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}