<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\Distributors;

use App\Dto\Stores\Distributors\DistributorDto;
use App\GraphQL\InputTypes\Stores\Distributors\DistributorInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Stores\DistributorType;
use App\Models\Stores\Distributor;
use App\Permissions\Stores\Distributors\DistributorUpdatePermission;
use App\Services\Stores\DistributorService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DistributorUpdateMutation extends BaseMutation
{
    public const NAME = 'distributorUpdate';
    public const PERMISSION = DistributorUpdatePermission::KEY;

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
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Distributor::class, 'id')]
            ],
            'input' => [
                'type' => DistributorInput::nonNullType(),
            ],
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
            fn() => $this->service->update(
                Distributor::query()->find($args['id']),
                DistributorDto::byArgs($args['input']),
            )
        );
    }
}
