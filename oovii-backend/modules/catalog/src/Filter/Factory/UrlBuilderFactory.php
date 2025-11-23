<?php

namespace WezomCms\Catalog\Filter\Factory;

use WezomCms\Catalog\Filter\Contracts\UrlBuilderInterface;
use WezomCms\Catalog\Filter\UrlBuilders\UrlBuilder;
use WezomCms\Catalog\Models\Category;

class UrlBuilderFactory
{
    /**
     * @param  Category  $category
     * @return UrlBuilderInterface
     */
    public static function category(Category $category): UrlBuilderInterface
    {
        return static::build('catalog.category.filter', 'catalog.category', $category->only('slug', 'id'));
    }

    /**
     * @param  array  $routeParams
     * @return UrlBuilderInterface
     */
    public static function search(array $routeParams = []): UrlBuilderInterface
    {
        return static::build('search.filter', 'search', $routeParams);
    }

    /**
     * @param  string  $filterRouteName
     * @param  string  $routeName
     * @param  array  $routeParams
     * @return UrlBuilder
     */
    protected static function build(string $filterRouteName, string $routeName, array $routeParams): UrlBuilder
    {
        $urlBuilder = new UrlBuilder($filterRouteName, $routeName);
        $urlBuilder->setRequest(app('request'));
        $urlBuilder->setRouteParameters($routeParams);

        return $urlBuilder;
    }
}
