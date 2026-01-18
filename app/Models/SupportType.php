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
class SupportType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}