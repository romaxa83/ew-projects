<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\Stores;

use App\Dto\Stores\Stores\StoreDto;
use App\GraphQL\InputTypes\Stores\Stores\StoreInputType;
use App\GraphQL\Types\Stores\StoreType;
use App\Models\Stores\Store;
use App\Permissions\Stores\Stores\StoreCreatePermission;
use App\Services\Stores\StoreService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StoreCreateMutation extends BaseMutation
{
    public const NAME = 'storeCreate';
    public const PERMISSION = StoreCreatePermission::KEY;

    public function __construct(private StoreService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return StoreType::nonNullType();
    }

    public function args(): array
    {
        return [
            'input' => StoreInputType::nonNullType(),
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
    ): Store {
        return makeTransaction(
            fn() => $this->service->create(StoreDto::byArgs($args['input']))
        );
    }
}
