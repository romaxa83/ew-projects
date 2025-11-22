<?php

namespace Tests\Traits\Chat;

use App\GraphQL\Queries\BackOffice\Chat\Conversations\ConversationQuery;
use App\GraphQL\Queries\BackOffice\Chat\Participants\ParticipantQuery;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Participation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\WithFaker;

trait InteractsWithChatHelper
{
    use WithFaker;

    protected function createConversation(Messageable $forUser): Conversation
    {
        return Conversation::factory()
            ->has(
                Participation::factory()->forUser($forUser),
                'participants'
            )
            ->create();
    }

    protected function getConversationQuery(array $args = []): array
    {
        return GraphQLQuery::query(ConversationQuery::NAME)
            ->args($args)
            ->select(
                [
                    'data' => $this->getConversationSelect()
                ]
            )
            ->make();
    }

    protected function getConversationSelect(): array
    {
        return [
            'id',
            'direct_message',
            'title',
            'description',
            'unread_messages_count',
            'avatar' => [
                'id',
                'name'
            ],
            'last_message' => [
                'body',
                'message_info' => [
                    'is_seen',
                    'is_sender',
                ],
                'participation' => [
                    'participant' => [
                        'name',
                        'email',
                        'phone',
                    ],
                ],
            ],
        ];
    }

    protected function getParticipationQuery(array $args = []): array
    {
        return GraphQLQuery::query(ParticipantQuery::NAME)
            ->args($args)
            ->select($this->getParticipationSelect())
            ->make();
    }

    protected function getParticipationSelect(): array
    {
        return [
            'id',
            'participant' => [
                'name',
                'email',
                'phone',
            ],
        ];
    }

    protected function sendMessage(Messageable $messageable, Conversation $conversation, string $message = null): void
    {
        Chat::message($message ?: 'hello!')
            ->from($messageable)
            ->to($conversation)
            ->send();
    }
}
