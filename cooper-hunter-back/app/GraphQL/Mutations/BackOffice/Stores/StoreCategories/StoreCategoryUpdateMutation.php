<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\StoreCategories;

use App\Dto\Stores\StoreCategories\StoreCategoryDto;
use App\GraphQL\InputTypes\Stores\StoreCategories\StoreCategoryInputType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Stores\StoreCategoryType;
use App\Models\Stores\StoreCategory;
use App\Permissions\Stores\StoreCategories\StoreCategoryUpdatePermission;
use App\Services\Stores\StoreCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StoreCategoryUpdateMutation extends BaseMutation
{
    public const NAME = 'storeCategoryUpdate';
    public const PERMISSION = StoreCategoryUpdatePermission::KEY;

    public function __construct(private StoreCategoryService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return StoreCategoryType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(StoreCategory::class, 'id')]
            ],
            'input' => [
                'type' => StoreCategoryInputType::nonNullType(),
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
    ): StoreCategory {
        $category = StoreCategory::query()->find($args['id']);

        return makeTransaction(
            fn() => $this->service->update(
                $category,
                StoreCategoryDto::byArgs($args['input'])
            )
        );
    }
}
