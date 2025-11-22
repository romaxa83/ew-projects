<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot\DeletePermission;
use App\Services\Catalog\Troubleshoots\TroubleshootService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TroubleshootDeleteMutation extends BaseMutation
{
    public const NAME = 'troubleshootDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(private TroubleshootService $service)
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
                    Rule::exists(Troubleshoot::class, 'id')
                ]
            ]
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): ResponseMessageEntity
    {
        try {
            $this->service->remove(
                Troubleshoot::find($args['id'])
            );

            return ResponseMessageEntity::success(__('messages.catalog.troubleshoots.troubleshoot.actions.delete.success.one-entity'));
        } catch (Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }
}


