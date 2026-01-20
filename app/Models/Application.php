<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Laravel\Eloquent\Filter\PartialSearchFilter;
use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Laravel\Eloquent\Filter\ExactFilter;
use ApiPlatform\Laravel\Eloquent\Filter\DateFilter;
use ApiPlatform\Laravel\Eloquent\Filter\RangeFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            securityMessage: "Authentication required to view applications."
        ),
        new Get(
            security: "is_granted('ROLE_USER') and (object.getUser() == user or object.getGuardian() == user or is_granted('ROLE_ADMIN') or (is_granted('ROLE_SCHOOL') and object.getOpportunity().getCreatedBy() == user))",
            securityMessage: "You can only view your own applications, applications you created as a guardian, or applications for opportunities you created as a school."
        ),
        new Post(
            security: "is_granted('ROLE_USER') and (is_granted('ROLE_STUDENT') or is_granted('ROLE_ADMIN'))",
            securityMessage: "Only students/parents and administrators can create applications."
        ),
        new Put(
            security: "is_granted('ROLE_USER') and (object.getUser() == user or object.getGuardian() == user or is_granted('ROLE_ADMIN'))",
            securityMessage: "You can only update your own applications or applications you created as a guardian."
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only administrators can delete applications."
        ),
        new Post(
            uriTemplate: '/applications/{id}/submit',
            controller: \App\Http\Controllers\SubmitApplicationController::class,
            security: "is_granted('ROLE_USER') and (object.getUser() == user or object.getGuardian() == user or is_granted('ROLE_ADMIN'))",
            securityMessage: "You can only submit your own applications or applications you created as a guardian.",
            name: 'submit_application'
        ),
        new Put(
            uriTemplate: '/applications/{id}/review',
            controller: \App\Http\Controllers\ReviewApplicationController::class,
            security: "is_granted('ROLE_SCHOOL') and object.getOpportunity().getCreatedBy() == user or is_granted('ROLE_ADMIN')",
            securityMessage: "You can only review applications for opportunities you created or be an administrator.",
            name: 'review_application'
        )
    ],
    middleware: ['auth:sanctum'],
    paginationItemsPerPage: 15,
    paginationMaximumItemsPerPage: 50,
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true
)]
// Status-based filtering
#[QueryParameter(key: 'status', filter: ExactFilter::class)]
#[QueryParameter(key: 'user_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'guardian_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'opportunity_id', filter: ExactFilter::class)]
#[QueryParameter(key: 'reviewed_by', filter: ExactFilter::class)]
// Date filters for submission and review dates
#[QueryParameter(key: 'submitted_at[before]', filter: DateFilter::class)]
#[QueryParameter(key: 'submitted_at[after]', filter: DateFilter::class)]
#[QueryParameter(key: 'reviewed_at[before]', filter: DateFilter::class)]
#[QueryParameter(key: 'reviewed_at[after]', filter: DateFilter::class)]
// Score range filtering
#[QueryParameter(key: 'score[gte]', filter: RangeFilter::class)]
#[QueryParameter(key: 'score[lte]', filter: RangeFilter::class)]
#[QueryParameter(key: 'score[between]', filter: RangeFilter::class)]
// Sorting options
#[QueryParameter(key: 'order[submitted_at]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[reviewed_at]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[score]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[created_at]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[status]', filter: OrderFilter::class)]
class Application extends Model
{
    protected $fillable = [
        'user_id',
        'guardian_id',
        'student_profile_id',
        'opportunity_id',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'score',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function applicationData(): HasMany
    {
        return $this->hasMany(ApplicationData::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function promotionalConversions(): HasMany
    {
        return $this->hasMany(PromotionalConversion::class);
    }
}