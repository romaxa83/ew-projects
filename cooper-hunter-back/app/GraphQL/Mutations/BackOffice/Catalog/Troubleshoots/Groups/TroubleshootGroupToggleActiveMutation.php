<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups;

use App\GraphQL\Types\Catalog\Troubleshoots\Groups;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Group\UpdatePermission;
use App\Services\Catalog\Troubleshoots\GroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TroubleshootGroupToggleActiveMutation extends BaseMutation
{
    public const NAME = 'troubleshootGroupToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private GroupService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Groups\TroubleshootGroupType::type();
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

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Model
    {
        return $this->service->toggleActive(
            Group::find($args['id'])
        );
    }
}

