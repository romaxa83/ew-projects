<?php

namespace App\GraphQL\Mutations\BackOffice\Menu;

use App\GraphQL\Types\Menu\MenuType;
use App\GraphQL\Types\NonNullType;
use App\Models\Menu\Menu;
use App\Permissions\Menu\MenuUpdatePermission;
use App\Services\Menu\MenuService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MenuToggleActiveMutation extends BaseMutation
{
    public const NAME = 'menuToggleActive';
    public const PERMISSION = MenuUpdatePermission::KEY;

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
        return MenuType::type();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Menu
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Menu
    {
        return makeTransaction(
            fn() => $this->service->toggleActive(
                Menu::find($args['id'])
            )
        );
    }
}
