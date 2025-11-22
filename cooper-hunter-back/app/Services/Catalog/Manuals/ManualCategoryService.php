<?php

namespace App\Services\Catalog\Manuals;

use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Products\Product;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class ManualCategoryService
{
    public function getCategories(): Collection
    {
        $parent = Category::query()
            ->cooper()
            ->whereNull('parent_id')
            ->where('active', true)
            ->joinTranslation()
            ->addSelect(Category::TABLE . '.id as category_id')
            ->addSelect(CategoryTranslation::TABLE . '.title as category_name')
            ->latest('sort')
            ->toBase()
            ->get()
            ->keyBy('category_id');

        $children = Category::query()
            ->whereIn('parent_id', $parent->pluck('category_id'))
            ->where('active', true)
            ->joinTranslation()
            ->addSelect(Category::TABLE . '.id as category_id')
            ->addSelect(Category::TABLE . '.parent_id')
            ->addSelect(CategoryTranslation::TABLE . '.title as category_name')
            ->toBase()
            ->get();

        foreach ($children as $child) {
            $p = $parent->get($child->parent_id);

            empty($p->sub_categories)
                ? $p->sub_categories = collect([$child])
                : $p->sub_categories->push($child);
        }

        foreach ($parent as $p) {
            if (empty($p->sub_categories)) {
                $p->sub_categories = collect([$p]);
            }
        }

        return $parent;
    }

    /**
     * @throws Exception
     */
    public function getProductsForCategory(int|Category $category, ?string $search = null): Collection
    {
        $ids = categoryStorage()->getAllChildrenIds($category);

        //todo: что если товаров будет миллион?
        $products = Product::query()
            ->whereIn('category_id', $ids)
            ->when($search, static fn(Builder|Product $b) => $b->filter(['query' => $search]))
            ->with(
                [
                    'category' => static fn(BelongsTo|Category $to) => $to
                        ->with('translation')
                        ->with(
                            [
                                'parent' => static fn(BelongsTo|Category $to) => $to
                                    ->with('translation')
                                    ->addSelect(
                                        [
                                            Category::TABLE . '.id',
                                        ]
                                    )
                            ]
                        )
                        ->addSelect(
                            [
                                Category::TABLE . '.id',
                                Category::TABLE . '.parent_id',
                            ]
                        )
                ]
            )
            ->leftJoin('manual_product', fn(JoinClause $j) => $j
                ->on(
                    Product::TABLE . '.id',
                    '=',
                    'manual_product.product_id'
                )
            )
            ->addSelect(
                [
                    Product::TABLE . '.id',
                    Product::TABLE . '.category_id',
                    Product::TABLE . '.title',
                    Product::TABLE . '.slug',
                ]
            )
            ->groupBy(Product::TABLE . '.id')
            ->simple()
            ->get();

        $categories = collect();

        foreach ($products as $product) {
            $categoryTitle = $product->category->translation->title;
            $categoryId = $product->category->id;

            if ($product->category->parent ?? null) {
                $categoryTitle .= " (" . $product->category->parent->translation->title . ")";
            }

            if ($c = $categories->get($categoryId)) {
                $c['products']->push($product);
            } else {
                $categories->put(
                    $categoryId,
                    [
                        'category_name' => $categoryTitle,
                        'products' => collect([$product])
                    ]
                );
            }
        }

        return $categories;
    }
}
