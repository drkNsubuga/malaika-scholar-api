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
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
class Document extends Model
{
    protected $fillable = [
        'user_id',
        'document_type_id',
        'documentable_type',
        'documentable_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'document_type',
        'status',
        'is_public',
        'verification_notes',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}