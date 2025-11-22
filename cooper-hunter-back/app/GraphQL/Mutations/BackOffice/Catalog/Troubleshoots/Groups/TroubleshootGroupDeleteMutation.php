<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Group\DeletePermission;
use App\Services\Catalog\Troubleshoots\GroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TroubleshootGroupDeleteMutation extends BaseMutation
{
    public const NAME = 'troubleshootGroupDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(private GroupService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Group::class, 'id')
                ]
            ]
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): ResponseMessageEntity
    {
        try {
            $this->service->remove(
                Group::find($args['id'])
            );
            return ResponseMessageEntity::success(__('messages.catalog.troubleshoots.group.actions.delete.success.one-entity'));
        } catch (Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }
}



