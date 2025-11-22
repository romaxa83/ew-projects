<?php


namespace App\GraphQL\Queries\FrontOffice\Chat\Menu;


use App\GraphQL\Types\Chat\ChatMenuType;
use App\Permissions\Chat\Menu\ChatMenuListPermission;
use App\Services\Chat\ChatMenuService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class ChatMenusQuery extends BaseQuery
{
    public const NAME = 'chatMenus';
    public const PERMISSION = ChatMenuListPermission::KEY;

    public function __construct(private ChatMenuService $service)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return ChatMenuType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getForChat($args, $fields);
    }
}
