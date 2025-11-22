<?php


namespace App\GraphQL\Mutations\Common\Clients;


use App\Dto\Clients\ClientDto;
use App\GraphQL\InputTypes\Clients\ClientInputType;
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

abstract class BaseClientUpdateMutation extends BaseMutation
{
    public const NAME = 'clientUpdate';
    public const PERMISSION = ClientUpdatePermission::KEY;

    public function __construct(private ClientService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

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
            'client' => [
                'type' => ClientInputType::nonNullType()
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
            fn() => $this->service->update(
                ClientDto::byArgs($args['client']),
                Client::find($args['id'])
            )
        );
    }
}
