<?php


namespace App\GraphQL\Mutations\Common\Clients;


use App\Dto\Clients\ClientDto;
use App\GraphQL\InputTypes\Clients\ClientInputType;
use App\GraphQL\Types\Clients\ClientType;
use App\Models\Clients\Client;
use App\Permissions\Clients\ClientCreatePermission;
use App\Services\Clients\ClientService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseClientCreateMutation extends BaseMutation
{
    public const NAME = 'clientCreate';
    public const PERMISSION = ClientCreatePermission::KEY;

    public function __construct(private ClientService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    public function args(): array
    {
        return [
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
            fn() => $this->service->create(ClientDto::byArgs($args['client']))
        );
    }
}
