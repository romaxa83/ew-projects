<?php


namespace App\GraphQL\Queries\BackOffice\Chat\Menu;


use App\GraphQL\Types\Chat\ChatMenuType;
use App\GraphQL\Types\Enums\Chat\ChatMenuActionEnumType;
use App\Models\Chat\ChatMenu;
use App\Permissions\Chat\Menu\ChatMenuListPermission;
use App\Services\Chat\ChatMenuService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class ChatMenusQuery extends BaseQuery
{
    public const NAME = 'chatMenus';
    public const PERMISSION = ChatMenuListPermission::KEY;

    public function __construct(private ChatMenuService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id()
                ],
                'per_page' => [
                    'type' => Type::int(),
                    'defaultValue' => config('queries.default.pagination.per_page')
                ],
                'page' => [
                    'type' => Type::int(),
                    'defaultValue' => 1
                ],
                'active' => [
                    'type' => Type::boolean(),
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Filter by translations title'
                ],
                'action' => [
                    'type' => ChatMenuActionEnumType::type()
                ],
                'without_parent' => [
                    'type' => Type::boolean()
                ],
            ],
            $this->sortArgs('sort-desc')
        );
    }

    public function type(): Type
    {
        return ChatMenuType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->list($args);
    }

    protected function allowedForSortFields(): array
    {
        return ChatMenu::ALLOWED_SORTING_FIELDS;
    }
}
