<?php

namespace Core\Chat\Services;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Events\MessageWasSent;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Message;
use Core\Chat\Models\MessageNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MessageNotificationService
{
    public function markAsRead(
        Conversation $conversation,
        Messageable $messageable,
        array $messageIds
    ): int {
        return $this->notificationForMessageableQuery($conversation, $messageable)
            ->whereIn('message_id', $messageIds)
            ->where('is_seen', false)
            ->update(
                [
                    'is_seen' => true,
                ]
            );
    }

    protected function notificationForMessageableQuery(Conversation $conversation, Messageable $messageable): Builder
    {
        return $this->query()
            ->where('conversation_id', $conversation->id)
            ->where(
                [
                    'messageable_id' => $messageable->getKey(),
                    'messageable_type' => $messageable->getMorphClass(),
                ]
            );
    }

    protected function query(): Builder|MessageNotification
    {
        return MessageNotification::query();
    }

    public function markAsReadAll(Conversation $conversation, Messageable $messageable): int
    {
        return $this->notificationForMessageableQuery($conversation, $messageable)
            ->where('is_seen', false)
            ->update(
                [
                    'is_seen' => true,
                ]
            );
    }

    public function createMessageNotifications(Message $message): void
    {
        $conversation = $message->conversation;

        $conversation->participants()->chunk(
            500,
            function (Collection $participations) use ($conversation, $message) {
                $notification = [];

                foreach ($participations as $participation) {
                    $is_sender = $message->participation_id === $participation->id;

                    $notification[] = [
                        'messageable_id' => $participation->messageable_id,
                        'messageable_type' => $participation->messageable_type,
                        'message_id' => $message->id,
                        'participation_id' => $participation->id,
                        'conversation_id' => $conversation->id,
                        'is_seen' => $message->mark_seen ?: $is_sender,
                        'is_sender' => $is_sender,
                        'created_at' => $message->created_at,
                    ];
                }

                $this->query()->insert($notification);
            }
        );

        event(new MessageWasSent($message));
    }
}
