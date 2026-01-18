<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete()
    ],
    paginationItemsPerPage: 12
)]
class Opportunity extends Model
{
    protected $fillable = [
        // Original fields
        'school_name', 'location', 'education_level', 'support_type',
        'eligibility', 'status', 'description', 'deadline', 'category',
        'coverage_percentage', 'duration', 'available_slots',
        'detailed_eligibility', 'application_requirements',
        'selection_criteria', 'contact_info', 'sponsor_id', 'is_hot',
        
        // Normalized relationship fields
        'school_id', 'education_level_id', 'support_type_id', 'category_id', 'created_by',
        'title', 'eligibility_summary', 'duration_months',
        
        // Promotional fields
        'is_featured', 'is_premium', 'is_sponsored', 'promotion_priority',
        'promotion_expires_at', 'total_impressions', 'total_clicks',
        'total_applications_from_promotion', 'featured_image_url',
        'promotional_text', 'sponsor_logo_url'
    ];
    
    protected $casts = [
        'detailed_eligibility' => 'array',
        'application_requirements' => 'array',
        'selection_criteria' => 'array',
        'contact_info' => 'array',
        'deadline' => 'datetime',
        'is_hot' => 'boolean',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'is_sponsored' => 'boolean',
        'promotion_expires_at' => 'datetime',
        'coverage_percentage' => 'decimal:2',
    ];
    
    // Normalized relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function supportType(): BelongsTo
    {
        return $this->belongsTo(SupportType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OpportunityCategory::class, 'category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Existing relationships
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }
    
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // Promotional relationships
    public function promotedOpportunities(): HasMany
    {
        return $this->hasMany(PromotedOpportunity::class);
    }

    // Normalized detail relationships
    public function eligibilityCriteria(): HasMany
    {
        return $this->hasMany(OpportunityEligibility::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(OpportunityRequirement::class);
    }

    public function selectionCriteria(): HasMany
    {
        return $this->hasMany(OpportunitySelectionCriterion::class);
    }

    // Helper methods for promotion status
    public function getIsCurrentlyPromotedAttribute(): bool
    {
        return $this->promotedOpportunities()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->exists();
    }

    public function getPromotionLevelAttribute(): string
    {
        if ($this->is_sponsored) return 'Sponsored';
        if ($this->is_premium) return 'Premium';
        if ($this->is_featured) return 'Featured';
        return 'None';
    }
}
