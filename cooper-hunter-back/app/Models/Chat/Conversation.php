<?php

namespace App\Models\Chat;

use App\Filters\Chat\Conversations\ConversationFilter;

class Conversation extends \Core\Chat\Models\Conversation
{
    public const MORPH_NAME = 'chat_conversation';

    protected $fillable = [
        'direct_message',
        'title',
        'description',
        'is_closed',
        'can_messaging',
    ];

    protected $casts = [
        'direct_message' => 'bool',
        'is_closed' => 'bool',
        'can_messaging' => 'bool',
    ];

    public function modelFilter(): string
    {
        return ConversationFilter::class;
    }
}
