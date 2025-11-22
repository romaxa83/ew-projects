<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\Distributors;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\Distributor;
use App\Permissions\Stores\Distributors\DistributorDeletePermission;
use App\Services\Stores\DistributorService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DistributorDeleteMutation extends BaseMutation
{
    public const NAME = 'distributorDelete';
    public const PERMISSION = DistributorDeletePermission::KEY;

    public function __construct(private DistributorService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
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
    ): ResponseMessageEntity {
        makeTransaction(fn() => $this->service->delete(Distributor::query()->find($args['id'])));

        return ResponseMessageEntity::success(__('Entity deleted'));
    }
}
