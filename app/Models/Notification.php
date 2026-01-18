<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put()
    ]
)]
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type_id',
        'notification_channel_id',
        'template_id',
        'title',
        'message',
        'type',
        'channel',
        'status',
        'metadata',
        'sent_at',
        'delivered_at',
        'read_at',
        'retry_count',
        'failure_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }
}