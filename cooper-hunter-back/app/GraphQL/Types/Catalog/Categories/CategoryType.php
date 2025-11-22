<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Catalog\CategoryTypeEnumType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Services\Catalog\Categories\CategoryService;
use App\Services\Catalog\Categories\CategoryStorageService;
use App\Traits\GraphQL\HasGuidTrait;
use GraphQL\Type\Definition\Type;

class CategoryType extends BaseType
{
    use HasGuidTrait;

    public const NAME = 'CategoryType';
    public const MODEL = Category::class;

    public function fields(): array
    {
        $fields = [
            'id' => [
                'type' => NonNullType::id()
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'breadcrumbs' => [
                'type' => CategoryBreadcrumbType::list(),
                'description' => 'Breadcrumbs are allowed only on single category page',
                'selectable' => false,
                'is_relation' => false,
                'always' => 'id'
            ],
            'sort' => [
                'type' => NonNullType::int()
            ],
            'active' => [
                'type' => NonNullType::boolean()
            ],
            'main' => [
                'type' => NonNullType::boolean()
            ],
            'type' => [
                'type' => CategoryTypeEnumType::type(),
            ],
            'seer' => [
                'type' => Type::float(),
                'selectable' => false,
                'is_relation' => false,
                'resolve' => fn(Category $category) => resolve(CategoryService::class)->getCategorySeer($category)
            ],
            'enable_seer' => [
                'type' => NonNullType::boolean(),
            ],
            'products_count' => [
                /** @see CategoryType::resolveProductsCountField() */
                'type' => NonNullType::string(),
                'selectable' => false,
            ],
            'image' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(Category $c) => $c->getFirstMedia($c::MEDIA_COLLECTION_NAME),
            ],
            'poster' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(Category $c) => $c->getFirstMedia($c::POSTER_COLLECTION_NAME),
            ],
            'parent' => [
                'type' => self::type(),
                'always' => 'parent_id',
                'is_relation' => true,
            ],
            'children' => [
                'type' => Type::listOf(self::nonNullType()),
                'is_relation' => true,
            ],
            'translation' => [
                'type' => CategoryTranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => NonNullType::listOf(CategoryTranslateType::nonNullType()),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            parent::fields(),
            $this->getGuidField(),
            $fields
        );
    }

    protected function resolveProductsCountField(Category $c): string
    {
        $count = app(CategoryStorageService::class)->getTotalCountForCategory($c);

        return trans_choice('messages.products_count', $count, compact('count'));
    }
}

