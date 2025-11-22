<?php

namespace App\GraphQL\Queries\Common\Menu;

use App\GraphQL\Types\Enums\Menu\MenuBlockTypeEnum;
use App\GraphQL\Types\Enums\Menu\MenuPositionTypeEnum;
use App\GraphQL\Types\Menu\MenuType;
use App\Models\Menu\Menu;
use App\Services\Menu\MenuService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseMenuQuery extends BaseQuery
{
    public const NAME = 'menu';

    public function __construct(private MenuService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id(),
                    'rules' => [
                        'nullable',
                        'int',
                        Rule::exists(Menu::class, 'id')
                    ]
                ],
                'position' => [
                    'type' => MenuPositionTypeEnum::type(),
                ],
                'block' => [
                    'type' => MenuBlockTypeEnum::type(),
                ],
                'query' => [
                    'type' => Type::string(),
                ],
            ],
            $this->sortArgs()
        );
    }

    public function type(): Type
    {
        return MenuType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getList(
            $args,
            $fields->getRelations(),
            $this->user()
        );
    }

    abstract protected function setQueryGuard(): void;

    protected function idRuleExists(): Exists
    {
        return Rule::exists(Menu::class, 'id');
    }

    protected function allowedForSortFields(): array
    {
        return Menu::ALLOWED_SORTING_FIELDS;
    }
}
