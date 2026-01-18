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
class NotificationTemplate extends Model
{
    protected $fillable = [
        'notification_type_id',
        'notification_channel_id',
        'name',
        'subject_template',
        'body_template',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'template_id');
    }
}