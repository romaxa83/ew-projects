<?php

namespace Core\Chat\GraphQL\Mutations;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\Models\Conversation;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

abstract class BaseSendMessageMutation extends BaseChatQuery
{
    public const NAME = 'chatSendMessage';

    public function args(): array
    {
        return [
            'text' => [
                'type' => Type::string(),
                'description' => 'Text of the message. Can be empty, if the message is an attachment or an image',
            ],
            'attachments' => [
                'type' => Type::listOf(GraphQL::type('Upload')),
            ],
        ];
    }

    protected function sendMessage(array $args, Messageable $messageable, Conversation $conversation): void
    {
        Chat::message($args['text'])
            ->attachments($args['attachments'] ?? [])
            ->from($messageable)
            ->to($conversation)
            ->send();
    }
}
