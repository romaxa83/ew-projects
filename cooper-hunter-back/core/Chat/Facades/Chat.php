<?php

namespace Core\Chat\Facades;

use Core\Chat\Manager\ChatManager;
use Core\Chat\Models\BaseChatModel;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Message;
use Core\Chat\Models\MessageNotification;
use Core\Chat\Models\Participation;
use Core\Chat\Repositories\ConversationRepository;
use Core\Chat\Repositories\MessageRepository;
use Core\Chat\Repositories\ParticipationRepository;
use Core\Chat\Services\ConversationService;
use Core\Chat\Services\MessageService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getConversationTable()
 * @method static BaseChatModel|Conversation getConversationModel()
 * @method static string getParticipationTable()
 * @method static BaseChatModel|Participation getParticipationModel()
 * @method static string getMessageTable()
 * @method static BaseChatModel|Message getMessageModel()
 * @method static string getMessageNotificationTable()
 * @method static BaseChatModel|MessageNotification getMessageNotificationModel()
 *
 * @method static ConversationRepository conversations()
 * @method static ConversationService conversation(Conversation $conversation = null)
 * @method static ParticipationRepository participants()
 * @method static MessageRepository messages()
 * @method static MessageService message(?string $text = null)
 *
 * @mixin ChatManager
 */
class Chat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChatManager::class;
    }
}
