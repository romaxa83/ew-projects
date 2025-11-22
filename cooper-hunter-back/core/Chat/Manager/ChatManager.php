<?php

namespace Core\Chat\Manager;

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

class ChatManager
{
    protected static BaseChatModel|Conversation $conversationModel;
    protected static BaseChatModel|Participation $participationModel;
    protected static BaseChatModel|Message $messageModel;
    protected static BaseChatModel|MessageNotification $messageNotificationModel;

    public function conversations(): ConversationRepository
    {
        return resolve(ConversationRepository::class)->reset();
    }

    public function conversation(Conversation $conversation = null): ConversationService
    {
        return resolve(ConversationService::class)
            ->reset()
            ->setConversation($conversation);
    }

    public function participants(): ParticipationRepository
    {
        return resolve(ParticipationRepository::class)->reset();
    }

    public function messages(): MessageRepository
    {
        return resolve(MessageRepository::class)->reset();
    }

    public function message(?string $text = null): MessageService
    {
        $messageService = resolve(MessageService::class)
            ->reset();

        if ($text) {
            $messageService->body($text);
        }

        return $messageService;
    }

    public function getConversationTable(): string
    {
        return $this->getConversationModel()->getTable();
    }

    public function getConversationModel(): BaseChatModel|Conversation
    {
        if (empty(self::$conversationModel)) {
            $model = config('chat.models.conversation');

            self::$conversationModel = new $model();
        }

        return self::$conversationModel;
    }

    public function getParticipationTable(): string
    {
        return $this->getParticipationModel()->getTable();
    }

    public function getParticipationModel(): BaseChatModel|Participation
    {
        if (empty(self::$participationModel)) {
            $model = config('chat.models.participation');

            self::$participationModel = new $model();
        }

        return self::$participationModel;
    }

    public function getMessageTable(): string
    {
        return $this->getMessageModel()->getTable();
    }

    public function getMessageModel(): BaseChatModel|Message
    {
        if (empty(self::$messageModel)) {
            $model = config('chat.models.message');

            self::$messageModel = new $model();
        }

        return self::$messageModel;
    }

    public function getMessageNotificationTable(): string
    {
        return $this->getMessageNotificationModel()->getTable();
    }

    public function getMessageNotificationModel(): BaseChatModel|MessageNotification
    {
        if (empty(self::$messageNotificationModel)) {
            $model = config('chat.models.message_notification');

            self::$messageNotificationModel = new $model();
        }

        return self::$messageNotificationModel;
    }
}
