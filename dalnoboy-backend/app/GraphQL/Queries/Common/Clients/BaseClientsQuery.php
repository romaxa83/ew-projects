<?php


namespace App\GraphQL\Queries\Common\Clients;


use App\GraphQL\Types\Clients\ClientType;
use App\Models\Clients\Client;
use App\Permissions\Clients\ClientShowPermission;
use App\Services\Clients\ClientService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseClientsQuery extends BaseQuery
{
    public const NAME = 'clients';
    public const PERMISSION = ClientShowPermission::KEY;

    public function __construct(private ClientService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = $this->buildArgs(
            Client::ALLOWED_SORTING_FIELDS,
            [
                'name',
                'contact_person',
                'phone',
                'edrpou',
                'manager_full_name',
                'manage_phone'
            ]
        );

        $args['sort']['defaultValue'] = [
            'name-asc'
        ];

        return $args;
    }

    public function type(): Type
    {
        return ClientType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show($args, $fields->getRelations());
    }
}
