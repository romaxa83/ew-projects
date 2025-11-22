<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\Stores;

use App\Dto\Stores\Stores\StoreDto;
use App\GraphQL\InputTypes\Stores\Stores\StoreInputType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Stores\StoreType;
use App\Models\Stores\Store;
use App\Permissions\Stores\Stores\StoreUpdatePermission;
use App\Services\Stores\StoreService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StoreUpdateMutation extends BaseMutation
{
    public const NAME = 'storeUpdate';
    public const PERMISSION = StoreUpdatePermission::KEY;

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
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Store::class, 'id')]
            ],
            'input' => [
                'type' => StoreInputType::nonNullType(),
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
    ): Store {
        $category = Store::query()->find($args['id']);

        return makeTransaction(
            fn() => $this->service->update(
                $category,
                StoreDto::byArgs($args['input'])
            )
        );
    }
}
