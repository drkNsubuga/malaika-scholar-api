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
use App\Traits\HasApiSecurity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only administrators can view all users."
        ),
        new Get(
            security: "is_granted('ROLE_USER') and (object == user or is_granted('ROLE_ADMIN'))",
            securityMessage: "You can only view your own profile or be an administrator."
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only administrators can create users directly."
        ),
        new Put(
            security: "is_granted('ROLE_USER') and (object == user or is_granted('ROLE_ADMIN'))",
            securityMessage: "You can only update your own profile or be an administrator."
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only administrators can delete users."
        )
    ],
    middleware: ['auth:sanctum'],
    paginationItemsPerPage: 20,
    paginationMaximumItemsPerPage: 100,
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true
)]
#[QueryParameter(key: 'name', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'email', filter: PartialSearchFilter::class)]
#[QueryParameter(key: 'role', filter: ExactFilter::class)]
#[QueryParameter(key: 'is_active', filter: BooleanFilter::class)]
#[QueryParameter(key: 'order[name]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[email]', filter: OrderFilter::class)]
#[QueryParameter(key: 'order[created_at]', filter: OrderFilter::class)]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasApiSecurity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar_url',
        'is_active',
        'preferences',
        'backup_guardian_email',
        'emergency_contact_email',
        'emergency_activated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'emergency_contact_email', // Hide sensitive emergency contact info
        'backup_guardian_email',   // Hide backup guardian info
    ];

    /**
     * The attributes that should be visible in API responses.
     * This helps control field visibility for different user roles.
     *
     * @var list<string>
     */
    protected $visible = [
        'id',
        'name',
        'email',
        'role',
        'phone',
        'avatar_url',
        'is_active',
        'preferences',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_active' => 'boolean',
            'emergency_activated_at' => 'datetime',
        ];
    }

    // Relationships for family account management
    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'guardian_id');
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    // Relationships for school management
    public function school(): HasOne
    {
        return $this->hasOne(School::class);
    }

    // Relationships for sponsorship
    public function sponsorshipRelationships(): HasMany
    {
        return $this->hasMany(SponsorshipRelationship::class, 'sponsor_id');
    }

    // Relationships for promotions
    public function promotionalPurchases(): HasMany
    {
        return $this->hasMany(PromotionalPurchase::class, 'purchaser_id');
    }

    // Relationships for AI assistant
    public function aiAssistantMessages(): HasMany
    {
        return $this->hasMany(AiAssistantMessage::class);
    }

    public function aiConversationSessions(): HasMany
    {
        return $this->hasMany(AiConversationSession::class);
    }

    // Existing relationships
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // Role helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isSchool(): bool
    {
        return $this->role === 'School';
    }

    public function isSponsor(): bool
    {
        return $this->role === 'Sponsor';
    }

    public function isDonor(): bool
    {
        return $this->role === 'Donor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'Student/Parent';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageOpportunities(): bool
    {
        return $this->isSchool() || $this->isAdmin();
    }

    public function canApplyToOpportunities(): bool
    {
        return $this->isStudent() || $this->isAdmin();
    }

    public function canSponsorStudents(): bool
    {
        return $this->isSponsor() || $this->isAdmin();
    }

    public function canDonateMaterials(): bool
    {
        return $this->isDonor() || $this->isSponsor() || $this->isAdmin();
    }

    public function canViewAnalytics(): bool
    {
        return $this->isSchool() || $this->isSponsor() || $this->isAdmin();
    }
}
