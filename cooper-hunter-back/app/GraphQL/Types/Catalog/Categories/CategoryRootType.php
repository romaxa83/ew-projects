<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Services\Catalog\Categories\CategoryStorageService;
use App\Traits\GraphQL\HasGuidTrait;
use GraphQL\Type\Definition\Type;

class CategoryRootType extends BaseType
{
    use HasGuidTrait;

    public const NAME = 'CategoryRootType';
    public const MODEL = Category::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id()],
            'sort' => ['type' => NonNullType::int()],
            'active' => ['type' => NonNullType::boolean()],
            'products_count' => [
                /** @see CategoryRootType::resolveProductsCountField() */
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
