<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageContent extends Model
{
    protected $table = 'homepage_content';
    
    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
        'content',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'background_image_url',
        'featured_image_url',
        'spotlight_data',
        'additional_data',
        'is_active',
        'display_order',
        'updated_by'
    ];

    protected $casts = [
        'spotlight_data' => 'array',
        'additional_data' => 'array',
        'is_active' => 'boolean'
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
