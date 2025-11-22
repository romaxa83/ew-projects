<?php

namespace App\GraphQL\Mutations\BackOffice\Menu;

use App\GraphQL\Types\NonNullType;
use App\Models\Menu\Menu;
use App\Permissions\Menu\MenuDeletePermission;
use App\Services\Menu\MenuService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MenuDeleteMutation extends BaseMutation
{
    public const NAME = 'menuDelete';
    public const PERMISSION = MenuDeletePermission::KEY;

    public function __construct(protected MenuService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Menu::class, 'id')
                ]
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                Menu::find($args['id'])
            )
        );
    }
}
