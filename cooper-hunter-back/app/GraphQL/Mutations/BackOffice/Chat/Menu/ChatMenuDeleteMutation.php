<?php


namespace App\GraphQL\Mutations\BackOffice\Chat\Menu;


use App\GraphQL\Types\NonNullType;
use App\Models\Chat\ChatMenu;
use App\Permissions\Chat\Menu\ChatMenuDeletePermission;
use App\Services\Chat\ChatMenuService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ChatMenuDeleteMutation extends BaseMutation
{
    public const NAME = 'chatMenuDelete';
    public const PERMISSION = ChatMenuDeletePermission::KEY;

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
            ]
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
                ChatMenu::find($args['id'])
            )
        );
    }
}
