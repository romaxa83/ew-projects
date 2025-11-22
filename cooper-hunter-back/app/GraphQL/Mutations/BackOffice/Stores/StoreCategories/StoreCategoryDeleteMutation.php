<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\StoreCategories;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\StoreCategory;
use App\Permissions\Stores\StoreCategories\StoreCategoryDeletePermission;
use App\Services\Stores\StoreCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StoreCategoryDeleteMutation extends BaseMutation
{
    public const NAME = 'storeCategoryDelete';
    public const PERMISSION = StoreCategoryDeletePermission::KEY;

    public function __construct(private StoreCategoryService $service)
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
                'rules' => [Rule::exists(StoreCategory::class, 'id')]
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
                StoreCategory::query()
                    ->find($args['id'])
            )
        );

        return ResponseMessageEntity::success(__('Entity deleted'));
    }
}
