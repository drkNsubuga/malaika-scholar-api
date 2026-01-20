<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Laravel\Eloquent\Filter\PartialSearchFilter;
use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Laravel\Eloquent\Filter\ExactFilter;
use ApiPlatform\Laravel\Eloquent\Filter\BooleanFilter;
use ApiPlatform\Laravel\Eloquent\Filter\DateFilter;
use ApiPlatform\Laravel\Eloquent\Filter\RangeFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            securityMessage: "Authentication required to view opportunities."
        ),
        new Get(
            security: "is_granted('ROLE_USER')",
            securityMessage: "Authentication required to view opportunity details."
        ),
        new Post(
            security: "is_granted('ROLE_SCHOOL') or is_granted('ROLE_ADMIN')",
            securityMessage: "Only schools and administrators can create opportunities."
        ),
        new Put(
            security: "is_granted('ROLE_SCHOOL') and object.getCreatedBy() == user or is_granted('ROLE_ADMIN')",
            securityMessage: "You can only update opportunities you created or be an administrator."
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only administrators can delete opportunities."
        )
    ],
    middleware: ['auth:sanctum'],
    paginationItemsPerPage: 12,
    paginationMaximumItemsPerPage: 50,
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true
)]
// Search parameters for school name, location, category
#[QueryParameter(key: 'school_name', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'location', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'title', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'description', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'eligibility_summary', filter: PartialSearchFilter::class)]
// Exact filters for categories and types
#[QueryParameter(key: 'category_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'education_level_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'support_type_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'status', filter: ExactFilter::class)]
#[QueryParameter(key: 'school_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'sponsor_id', filter: ExactFilter::class)]
// Boolean filters
#[QueryParameter(key: 'is_hot', filter: BooleanFilter::class)]
#[QueryParameter(key: 'is_featured', filter: BooleanFilter::class)]
#[QueryParameter(key: 'is_premium', filter: BooleanFilter::class)]
#[QueryParameter(key: 'is_sponsored', filter: BooleanFilter::class)]
// Date filters
#[QueryParameter(key: 'deadline[before]', filter: DateFilter::class)]
#[QueryParameter(key: 'deadline[after]', filter: DateFilter::class)]
#[QueryParameter(key: 'deadline[strictly_before]', filter: DateFilter::class)]
#[QueryParameter(key: 'deadline[strictly_after]', filter: DateFilter::class)]
// Range filters
#[QueryParameter(key: 'coverage_percentage[between]', filter: RangeFilter::class)]
#[QueryParameter(key: 'duration_months[between]', filter: RangeFilter::class)]
#[QueryParameter(key: 'available_slots[gte]', filter: RangeFilter::class)]
#[QueryParameter(key: 'available_slots[lte]', filter: RangeFilter::class)]
// Sorting by deadline, relevance, alphabetical order
#[QueryParameter(key: 'order[deadline]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[school_name]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[title]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[created_at]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[coverage_percentage]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[available_slots]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[promotion_priority]', filter: OrderFilter::class)]
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

    // Security helper method for API Platform
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    // Scope methods for common queries
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByEducationLevel($query, $levelId)
    {
        return $query->where('education_level_id', $levelId);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeByDeadlineAfter($query, $date)
    {
        return $query->where('deadline', '>', $date);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithAvailableSlots($query)
    {
        return $query->where('available_slots', '>', 0);
    }
}
