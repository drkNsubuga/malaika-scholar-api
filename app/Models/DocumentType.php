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
class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'allowed_formats',
        'max_file_size',
        'is_required_for_applications',
    ];

    protected $casts = [
        'allowed_formats' => 'array',
        'is_required_for_applications' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}