<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\Distributors;

use App\Dto\Stores\Distributors\DistributorDto;
use App\GraphQL\InputTypes\Stores\Distributors\DistributorInput;
use App\GraphQL\Types\Stores\DistributorType;
use App\Models\Stores\Distributor;
use App\Permissions\Stores\Distributors\DistributorCreatePermission;
use App\Services\Stores\DistributorService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DistributorCreateMutation extends BaseMutation
{
    public const NAME = 'distributorCreate';
    public const PERMISSION = DistributorCreatePermission::KEY;

    public function __construct(private DistributorService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return DistributorType::nonNullType();
    }

    public function args(): array
    {
        return [
            'input' => DistributorInput::nonNullType(),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Distributor {
        return makeTransaction(
            fn() => $this->service->create(DistributorDto::byArgs($args['input']))
        );
    }
}
