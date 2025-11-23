<?php

namespace WezomCms\ProductReviews\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Http\Request;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\ProductReviews\Models\ProductReview;

/**
 * Class ProductReviewFilter
 * @package WezomCms\ProductReviews\ModelFilters
 * @mixin ProductReview
 */
class ProductReviewFilter extends ModelFilter implements FilterListFieldsInterface
{
    protected $products = [];

    /**
     * Generate array with fields
     * @return iterable
     */
    public function getFields(): iterable
    {
        return [
            FilterField::makeName()->label(__('cms-product-reviews::admin.Name'))->size(2),
            FilterField::make()->name('email')->label(__('cms-product-reviews::admin.E-mail'))->size(2),
            FilterField::make()
                ->type(FilterField::TYPE_SELECT)
                ->options($this->products)
                ->name('product_id')
                ->label(__('cms-product-reviews::admin.Product'))
                ->class('js-ajax-select2')
                ->attributes([
                    'data-url' => route('admin.products.search')
                ]),
            FilterField::published(),
            FilterField::make()->name('created_at')->label(__('cms-product-reviews::admin.Date'))
                ->type(FilterField::TYPE_DATE_RANGE),
        ];
    }

    /**
     * @param  Request  $request
     */
    public function restoreSelectedOptions(Request $request)
    {
        if ($productId = $request->get('product_id')) {
            $product = Product::find($productId);
            if ($product) {
                $this->products = [$product->id => $product->name];
            }
        }
    }

    public function name($name): void
    {
        $this->whereLike('name', $name);
    }

    public function email($email): void
    {
        $this->whereLike('email', $email);
    }

    public function product($id): void
    {
        $this->where('product_id', $id);
    }

    public function published($published): void
    {
        $this->where('published', $published);
    }

    public function parent($parentId): void
    {
        $this->where('parent_id', $parentId);
    }

    public function createdAtFrom($date): void
    {
        $this->where('created_at', '>=', Carbon::parse($date));
    }

    public function createdAtTo($date): void
    {
        $this->where('created_at', '<=', Carbon::parse($date)->endOfDay());
    }
}
