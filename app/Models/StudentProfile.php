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
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
class StudentProfile extends Model
{
    protected $fillable = [
        'guardian_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'grade_level',
        'school_name',
        'student_id_number',
        'academic_performance',
        'achievements',
        'support_needs',
        'is_primary_account',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'academic_performance' => 'array',
        'achievements' => 'array',
        'support_needs' => 'array',
        'is_primary_account' => 'boolean',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function sponsorshipRelationships(): HasMany
    {
        return $this->hasMany(SponsorshipRelationship::class);
    }

    // Helper method to calculate age
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    // Helper method to determine access level based on age
    public function getAccessLevelAttribute(): string
    {
        $age = $this->age;
        
        if ($age < 13) {
            return 'no_access';
        } elseif ($age < 16) {
            return 'view_only';
        } elseif ($age < 18) {
            return 'collaborative';
        } else {
            return 'independent';
        }
    }
}