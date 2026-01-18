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

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
class SponsorshipRelationship extends Model
{
    protected $fillable = [
        'sponsor_id',
        'student_profile_id',
        'relationship_type',
        'amount_committed',
        'amount_paid',
        'duration_months',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'amount_committed' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    // Helper method to calculate remaining amount
    public function getRemainingAmountAttribute(): float
    {
        return $this->amount_committed - $this->amount_paid;
    }

    // Helper method to calculate completion percentage
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->amount_committed == 0) {
            return 0;
        }
        
        return ($this->amount_paid / $this->amount_committed) * 100;
    }
}