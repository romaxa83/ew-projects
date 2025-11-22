<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Manuals;

use App\GraphQL\Types\Catalog\Manuals\Categories\ManualCategoryType;
use App\Services\Catalog\Manuals\ManualCategoryService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class ManualCategoriesQuery extends BaseQuery
{
    public const NAME = 'manualCategories';
    public const DESCRIPTION = 'List of categories to be used with: "' . ManualCategoryQuery::NAME . '" query';

    public function __construct(protected ManualCategoryService $service)
    {
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return ManualCategoryType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getCategories();
    }
}
