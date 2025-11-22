<?php


namespace App\GraphQL\Mutations\BackOffice\Clients;


use App\Dto\Clients\ClientBanDto;
use App\GraphQL\InputTypes\Clients\ClientBanInputType;
use App\GraphQL\Types\Clients\ClientType;
use App\GraphQL\Types\NonNullType;
use App\Models\Clients\Client;
use App\Permissions\Clients\ClientUpdatePermission;
use App\Services\Clients\ClientService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ClientBanMutation extends BaseMutation
{
    public const NAME = 'clientBan';
    public const PERMISSION = ClientUpdatePermission::KEY;

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
            ],
            'ban' => [
                'type' => ClientBanInputType::type(),
                'description' => 'Set null for unban client'
            ]
        ];
    }

    public function type(): Type
    {
        return ClientType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Client
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Client
    {
        return makeTransaction(
            fn() => $this->service->ban(
                ClientBanDto::byArgs($args['ban'] ?? []),
                Client::find($args['id'])
            )
        );
    }
}
