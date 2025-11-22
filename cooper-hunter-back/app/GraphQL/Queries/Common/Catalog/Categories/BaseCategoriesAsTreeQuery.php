<?php

namespace App\GraphQL\Queries\Common\Catalog\Categories;

use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\Services\Catalog\Categories\CategoryStorageService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCategoriesAsTreeQuery extends BaseQuery
{
    public const NAME = 'categoriesListAsTree';

    public function args(): array
    {
        return [
            'with_olmo' => [
                'type' => Type::boolean(),
            ],
            'with_spares' => [
                'type' => Type::boolean(),
            ],
        ];
    }

    public function type(): Type
    {
        return CategoryType::list();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        /** @var $service CategoryStorageService */
        $service = app(CategoryStorageService::class);

        return $service->getCategoriesAsTree($this->getActive(), $args);
    }

    protected function getActive(): ?bool
    {
        return null;
    }
}
