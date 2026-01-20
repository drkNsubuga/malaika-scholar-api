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
use ApiPlatform\Laravel\Eloquent\Filter\BooleanFilter;
use ApiPlatform\Metadata\QueryParameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    paginationItemsPerPage: 50
)]
#[QueryParameter(key: 'name', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'is_required_for_applications', filter: BooleanFilter::class)]
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
        'max_file_size' => 'integer',
    ];

    protected $appends = [
        'max_file_size_formatted',
        'allowed_formats_string',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get formatted file size limit
     */
    public function getMaxFileSizeFormattedAttribute(): string
    {
        if (!$this->max_file_size) {
            return 'No limit';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($this->max_file_size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get allowed formats as comma-separated string
     */
    public function getAllowedFormatsStringAttribute(): string
    {
        if (!$this->allowed_formats || !is_array($this->allowed_formats)) {
            return '';
        }

        return implode(', ', array_map('strtoupper', $this->allowed_formats));
    }

    /**
     * Check if a file format is allowed
     */
    public function isFormatAllowed(string $format): bool
    {
        if (!$this->allowed_formats || !is_array($this->allowed_formats)) {
            return false;
        }

        return in_array(strtolower($format), array_map('strtolower', $this->allowed_formats));
    }

    /**
     * Check if file size is within limit
     */
    public function isSizeAllowed(int $fileSize): bool
    {
        if (!$this->max_file_size) {
            return true; // No limit set
        }

        return $fileSize <= $this->max_file_size;
    }

    /**
     * Scope for required document types
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required_for_applications', true);
    }

    /**
     * Scope for optional document types
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required_for_applications', false);
    }
}