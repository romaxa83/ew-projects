<?php

namespace App\GraphQL\Mutations\BackOffice\Security;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Security\IpAccessDeletePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class IpAccessDeleteMutation extends BaseIpAccessMutation
{
    public const NAME = 'ipAccessDelete';
    public const PERMISSION = IpAccessDeletePermission::KEY;

    public function args(): array
    {
        return [
            'ids' => [
                'type' => NonNullType::listOf(
                    NonNullType::id(),
                ),
            ],
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        try {
            $this->service->delete($args['ids']);

            return ResponseMessageEntity::success(
                __('messages.ip-access.ip-list-deleted')
            );
        } catch (Throwable $e) {
            logger($e);

            return ResponseMessageEntity::fail(
                __('messages.ip-access.ip-list-delete-error')
            );
        }
    }

    protected function rules(array $args = []): array
    {
        return $this->guest()
            ? []
            : [
                'ids' => ['required', 'array'],
                'ids.*' => parent::rules()['id'],
            ];
    }
}
