<?php


namespace App\GraphQL\Mutations\BackOffice\Clients;


use App\GraphQL\Types\NonNullType;
use App\Models\Clients\Client;
use App\Permissions\Clients\ClientDeletePermission;
use App\Services\Clients\ClientService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ClientDeleteMutation extends BaseMutation
{
    public const NAME = 'clientDelete';
    public const PERMISSION = ClientDeletePermission::KEY;

    public function __construct(private ClientService $service)
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
                    Rule::exists(Client::class, 'id')
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
                Client::find($args['id'])
            )
        );
    }
}
