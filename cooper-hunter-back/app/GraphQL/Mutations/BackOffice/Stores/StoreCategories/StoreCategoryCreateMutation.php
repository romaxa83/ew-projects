<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Stores\StoreCategories;

use App\Dto\Stores\StoreCategories\StoreCategoryDto;
use App\GraphQL\InputTypes\Stores\StoreCategories\StoreCategoryInputType;
use App\GraphQL\Types\Stores\StoreCategoryType;
use App\Models\Stores\StoreCategory;
use App\Permissions\Stores\StoreCategories\StoreCategoryCreatePermission;
use App\Services\Stores\StoreCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StoreCategoryCreateMutation extends BaseMutation
{
    public const NAME = 'storeCategoryCreate';
    public const PERMISSION = StoreCategoryCreatePermission::KEY;

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
            'input' => StoreCategoryInputType::nonNullType(),
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
        return makeTransaction(
            fn() => $this->service->create(StoreCategoryDto::byArgs($args['input']))
        );
    }
}
