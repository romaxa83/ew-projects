<?php

namespace App\GraphQL\Mutations\BackOffice\Menu;

use App\Dto\Menu\MenuDto;
use App\GraphQL\InputTypes\Menu\MenuInput;
use App\GraphQL\Types\Menu\MenuType;
use App\Models\Menu\Menu;
use App\Permissions\Menu\MenuCreatePermission;
use App\Services\Menu\MenuService;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MenuCreateMutation extends BaseMutation
{
    public const NAME = 'menuCreate';
    public const PERMISSION = MenuCreatePermission::KEY;

    public function __construct(protected MenuService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'menu' => [
                'type' => MenuInput::nonNullType(),
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
     * @throws InvalidEnumMemberException
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Menu
    {
        return makeTransaction(
            fn() => $this->service->create(
                MenuDto::byArgs($args['menu'])
            )
        );
    }
}
