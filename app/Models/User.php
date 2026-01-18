<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete()
    ],
    paginationItemsPerPage: 20
)]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
}
