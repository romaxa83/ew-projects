<?php

namespace Core\Chat\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class MessageNotification extends BaseChatModel
{
    use SoftDeletes;

    public const TABLE = 'chat_message_notifications';

    protected $table = self::TABLE;

    protected $fillable = [
        'messageable_id',
        'messageable_type',
        'message_id',
        'conversation_id'
    ];

    protected $casts = [
        'deleted_at' => 'datetime'
    ];
}
