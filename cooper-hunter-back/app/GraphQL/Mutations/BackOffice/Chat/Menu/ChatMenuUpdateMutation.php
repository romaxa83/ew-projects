<?php


namespace App\GraphQL\Mutations\BackOffice\Chat\Menu;


use App\Dto\Chat\Menu\ChatMenuDto;
use App\GraphQL\InputTypes\Chat\ChatMenuInputType;
use App\GraphQL\Types\Chat\ChatMenuType;
use App\GraphQL\Types\NonNullType;
use App\Models\Chat\ChatMenu;
use App\Permissions\Chat\Menu\ChatMenuUpdatePermission;
use App\Services\Chat\ChatMenuService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ChatMenuUpdateMutation extends BaseMutation
{
    public const NAME = 'chatMenuUpdate';
    public const PERMISSION = ChatMenuUpdatePermission::KEY;

    public function __construct(private ChatMenuService $service)
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
                    Rule::exists(ChatMenu::class, 'id')
                ]
            ],
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
            fn() => $this->service->update(
                ChatMenuDto::byArgs($args['chat_menu']),
                ChatMenu::find($args['id'])
            )
        );
    }
}
