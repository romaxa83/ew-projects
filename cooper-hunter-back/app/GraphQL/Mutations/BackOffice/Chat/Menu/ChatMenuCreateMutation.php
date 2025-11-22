<?php


namespace App\GraphQL\Mutations\BackOffice\Chat\Menu;


use App\Dto\Chat\Menu\ChatMenuDto;
use App\GraphQL\InputTypes\Chat\ChatMenuInputType;
use App\GraphQL\Types\Chat\ChatMenuType;
use App\Models\Chat\ChatMenu;
use App\Permissions\Chat\Menu\ChatMenuCreatePermission;
use App\Services\Chat\ChatMenuService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ChatMenuCreateMutation extends BaseMutation
{
    public const NAME = 'chatMenuCreate';
    public const PERMISSION = ChatMenuCreatePermission::KEY;

    public function __construct(private ChatMenuService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'chat_menu' => [
                'type' => ChatMenuInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return ChatMenuType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return mixed
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ChatMenu {
        return makeTransaction(
            fn() => $this->service->create(
                ChatMenuDto::byArgs($args['chat_menu'])
            )
        );
    }
}
