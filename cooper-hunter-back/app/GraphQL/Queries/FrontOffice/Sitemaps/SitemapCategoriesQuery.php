<?php

namespace App\GraphQL\Queries\FrontOffice\Sitemaps;

use App\GraphQL\Types\Catalog\Categories\CategoryForSitemapType;
use App\Models\Catalog\Categories\Category;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class SitemapCategoriesQuery extends BaseQuery
{
    public const NAME = 'sitemapCategories';

    public function type(): Type
    {
        return CategoryForSitemapType::list();
    }

    public function args(): array
    {
        return [];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return Category::getForSitemap();
    }
}
