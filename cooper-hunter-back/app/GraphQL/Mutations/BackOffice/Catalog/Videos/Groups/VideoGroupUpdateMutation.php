<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups;

use App\Dto\Catalog\Video\GroupDto;
use App\GraphQL\Types\Catalog\Videos\Groups;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Videos\Group\UpdatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\Video\GroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoGroupUpdateMutation extends BaseMutation
{
    public const NAME = 'videoGroupUpdate';
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
            ],
            'active' => [
                'type' => Type::boolean()
            ],
            'translations' => [
                'type' => Groups\TranslateInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Group
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Group
    {
        return makeTransaction(
            fn() => $this->service->update(
                GroupDto::byArgs($args),
                Group::find($args['id'])
            )
        );
    }
}
