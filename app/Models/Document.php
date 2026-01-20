<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Laravel\Eloquent\Filter\ExactFilter;
use ApiPlatform\Laravel\Eloquent\Filter\PartialSearchFilter;
use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Metadata\QueryParameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use App\Services\StorageService;

#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and (object.user == user or is_granted('ROLE_ADMIN'))"
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and (object.user == user or is_granted('ROLE_ADMIN'))"
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and (object.user == user or is_granted('ROLE_ADMIN'))"
        )
    ],
    middleware: ['auth:sanctum'],
    paginationItemsPerPage: 20
)]
#[QueryParameter(key: 'user_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'document_type_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'documentable_type', filter: ExactFilter::class)]
#[QueryParameter(key: 'documentable_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'status', filter: ExactFilter::class)]
#[QueryParameter(key: 'original_name', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'order[created_at]', filter: OrderFilter::class)]
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
        'file_size' => 'integer',
    ];

    protected $hidden = [
        'file_path', // Hide actual file path for security
    ];

    protected $appends = [
        'download_url',
        'file_size_formatted',
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

    /**
     * Get secure download URL for the document
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        $storageService = app(StorageService::class);
        return $storageService->getDocumentUrl($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($this->file_size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if document exists in storage
     */
    public function existsInStorage(): bool
    {
        if (!$this->file_path) {
            return false;
        }

        return Storage::disk('documents')->exists($this->file_path);
    }

    /**
     * Delete document file from storage
     */
    public function deleteFromStorage(): bool
    {
        if (!$this->file_path) {
            return true;
        }

        $storageService = app(StorageService::class);
        return $storageService->deleteFile($this->file_path, 'documents');
    }

    /**
     * Scope to filter by document owner
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by verification status
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope to filter by document status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Delete file from storage when document record is deleted
        static::deleting(function ($document) {
            $document->deleteFromStorage();
        });
    }
}