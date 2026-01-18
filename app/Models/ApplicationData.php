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
class ApplicationData extends Model
{
    protected $fillable = [
        'application_id',
        'field_name',
        'field_value',
        'field_type',
    ];

    protected $casts = [
        'field_value' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}