<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\Stores;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\Store;
use App\Permissions\Stores\Stores\StoreDeletePermission;
use App\Services\Stores\StoreService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StoreDeleteMutation extends BaseMutation
{
    public const NAME = 'storeDelete';
    public const PERMISSION = StoreDeletePermission::KEY;

    public function __construct(private StoreService $service)
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
                'rules' => [Rule::exists(Store::class, 'id')]
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
        makeTransaction(
            fn() => $this->service->delete(
                Store::query()
                    ->find($args['id'])
            )
        );

        return ResponseMessageEntity::success(__('Entity deleted'));
    }
}
