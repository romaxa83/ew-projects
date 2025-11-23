<?php

namespace WezomCms\Catalog\Widgets\Site;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

abstract class BaseProductsCarouselByFlag extends AbstractWidget
{
    public const LIMIT = 20;

    /**
     * @var null|Collection
     */
    protected static $rootCategories;

    /**
     * @var null|Collection
     */
    protected static $allCategories;

    /**
     * The number of minutes before cache expires.
     * False means no caching at all.
     * If debug mode is on - the widget will not be cached.
     *
     * @var int|float|bool
     */
    public $cacheTime = 1;

    /**
     * View name.
     *
     * @var string
     */
    protected $view = 'cms-catalog::site.widgets.products-tabs-by-categories';

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $result = $this->getByProductFlag($this->flagName());

        if ($result->isEmpty()) {
            return null;
        }

        return [
            'result' => $result,
            'heading' => $this->heading(),
            'ns' => $this->flagName(),
            'limit' => method_exists($this, 'limit') ? $this->limit() : static::LIMIT,
        ];
    }

    /**
     * Return product field name for filtering.
     *
     * @return string
     */
    abstract public function flagName(): string;

    /**
     * Return widget heading.
     *
     * @return string|null
     */
    abstract public function heading(): ?string;

    /**
     * @param  string  $flag
     * @return \Illuminate\Support\Collection
     */
    private function getByProductFlag(string $flag)
    {
        $rootCategories = $this->getRootCategories();

        if ($rootCategories->isEmpty()) {
            return collect();
        }

        $allCategories = $this->getAllCategories();

        $limit = method_exists($this, 'limit') ? $this->limit() : static::LIMIT;

        $result = collect();
        foreach ($rootCategories as $category) {
            $childIds = Helpers::getAllChildes($allCategories, $category->id);
            $childIds[] = $category->id;

            $products = Product::whereIn('category_id', $childIds)
                ->where($flag, true)
                ->published()
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            if ($products->isNotEmpty()) {
                $category->products = $products;
                $result[] = $category;
            }
        }

        return $result;
    }

    /**
     * @return Collection|null
     */
    protected function getRootCategories()
    {
        if (null === static::$rootCategories) {
            static::$rootCategories = Category::whereNull('parent_id')
                ->published()
                ->sorting()
                ->get();
        }

        return static::$rootCategories;
    }

    /**
     * @return Collection|null
     */
    protected function getAllCategories()
    {
        if (null === static::$allCategories) {
            static::$allCategories = Category::published()->get();
        }

        return static::$allCategories;
    }
}
