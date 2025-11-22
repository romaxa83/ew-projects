<?php

namespace App\GraphQL\Mutations\FrontOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Types\NonNullType;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Mutations\BaseSendMessageMutation;
use Core\Contracts\HasAvatar;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class SendMessageMutation extends BaseSendMessageMutation
{
    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'prohibit_messaging' => [
                    'type' => Type::boolean(),
                ],
            ]
        );
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @throws AuthorizationError
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $conversation = Chat::conversations()
            ->getQueryForUser(
                $technician = $this->getUser()
            )->first();

        if (is_null($conversation)) {
            $conversation = Chat::conversation()
                ->participants($technician)
                ->title($technician->getName())
                ->start();

            if ($technician instanceof HasAvatar && $technician->hasAvatar()) {
                Chat::conversation()
                    ->setAvatar($technician->getAvatarUrl());
            }
        }

        $this->sendMessage($args, $technician, $conversation);

        if ($conversation->is_closed) {
            $conversation->is_closed = false;
            $conversation->save();
        }

        if ($args['prohibit_messaging'] ?? false) {
            $conversation->can_messaging = false;
            $conversation->save();

            event(new ConversationIsProcessed($conversation, $technician));
        }

        return true;
    }
}
