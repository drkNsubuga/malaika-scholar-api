<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]
class NotificationType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationTemplates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class);
    }
}