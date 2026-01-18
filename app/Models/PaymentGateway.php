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
class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'configuration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configuration' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}