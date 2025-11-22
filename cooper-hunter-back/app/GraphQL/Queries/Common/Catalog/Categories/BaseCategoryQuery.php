<?php

namespace App\GraphQL\Queries\Common\Catalog\Categories;

use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\Models\Catalog\Categories\Category;
use App\Services\Catalog\Categories\CategoryStorageService;
use Core\GraphQL\Queries\BaseQuery;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCategoryQuery extends BaseQuery
{
    public const NAME = 'category';

    public function args(): array
    {
        return [
            'slug' => [
                'type' => Type::string(),
                'rules' => [
                    'nullable',
                    'string',
                    'required_without:id',
                    Rule::exists(Category::TABLE, 'slug'),
                ],
            ],
            'id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'integer',
                    'required_without:slug',
                    Rule::exists(Category::TABLE, 'id')
                ]
            ],
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
        return CategoryType::type();
    }

    /**
     * @throws Exception
     */
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Category
    {

        return $this->getCategory($args)?->setBreadcrumbs();
    }

    protected function getCategory(array $args): ?Category
    {
        return app(CategoryStorageService::class)
            ->getTreeForCategory(
                Category::query()
                    ->filter($args)
                    ->first(),
                $this->getActive(),
                $args
            );
    }

    protected function getActive(): ?bool
    {
        return null;
    }
}
