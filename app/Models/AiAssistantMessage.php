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
class AiAssistantMessage extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'message_type',
        'content',
        'context_data',
        'response_time_ms',
        'feedback_rating',
        'feedback_comment',
        'ai_model_version',
    ];

    protected $casts = [
        'context_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversationSession(): BelongsTo
    {
        return $this->belongsTo(AiConversationSession::class, 'session_id', 'session_id');
    }
}