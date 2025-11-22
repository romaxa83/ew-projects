<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups;

use App\GraphQL\Types\Catalog\Videos\Groups;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Videos\Group\UpdatePermission;
use App\Services\Catalog\Video\GroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoGroupToggleActiveMutation extends BaseMutation
{
    public const NAME = 'videoGroupToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private GroupService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Groups\VideoGroupType::type();
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

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Group
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Group
    {
        return makeTransaction(
            fn () => $this->service->toggleActive(
                Group::find($args['id'])
            )
        );
    }
}

