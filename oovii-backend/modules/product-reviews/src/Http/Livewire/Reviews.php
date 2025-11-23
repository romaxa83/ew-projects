<?php

namespace WezomCms\ProductReviews\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\ProductReviews\Enums\Ratings;
use WezomCms\ProductReviews\Enums\SortVariants;
use WezomCms\ProductReviews\Models\ProductReview;
use WezomCms\ProductReviews\Traits\InteractsWithLikes;

/**
 * Class Reviews
 * @package WezomCms\ProductReviews\Http\Livewire
 * @property Product $product
 */
class Reviews extends Component
{
    use InteractsWithLikes;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $loads = 1;

    /**
     * @var string
     */
    public $sort;

    /**
     * @var string[]
     */
    protected $listeners = ['updateProductId', 'updatedProductReviews' => '$refresh'];

    /**
     * @var array
     */
    public $sortVariants;

    /**
     * @param  Product  $product
     */
    public function mount(Product $product)
    {
        $this->productId = $product->id;

        $this->limit = (int)settings('product-reviews.site.product-page-limit', 10);

        $this->computedPropertyCache['product'] = $product;

        $this->sortVariants = SortVariants::asSelectArray();

        $this->sort = SortVariants::TOP;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        /** @var Builder|ProductReview $query */
        $query = $this->product->publishedReviews()->root();

        switch ($this->sort) {
            case SortVariants::OLDEST:
                $query->oldest();
                break;
            case SortVariants::LATEST:
                $query->latest();
                break;
            case SortVariants::TOP:
            default:
                $query->top();
        }

        $rootReviews = $query->take($this->limit * $this->loads)->get();

        $childrenReviews = $this->product->publishedReviews()
            ->onlyChildren()
            ->oldest()
            ->get();

        $reviews = Helpers::groupByParentId($childrenReviews);
        $reviews[null] = $rootReviews;

        $totalRootReviews = $this->product->published_reviews_count
            ?? $this->product->publishedReviews()->root()->count();

        return view('cms-product-reviews::site.livewire.reviews', [
            'ratings' => Ratings::getValues(),
            'product' => $this->product,
            'reviews' => $reviews,
            'hasMore' => $rootReviews->count() < $totalRootReviews,
        ]);
    }

    public function loadMore()
    {
        $this->loads++;
    }

    /**
     * @return Product
     */
    public function getProductProperty()
    {
        return Product::published()->findOrFail($this->productId);
    }

    /**
     * Listen other product components when updated productId property.
     *
     * @param $id
     */
    public function updateProductId($id)
    {
        $this->productId = $id;

        $this->reset('loads');
    }
}
