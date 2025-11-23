<?php

namespace WezomCms\ProductReviews\Http\Livewire;

use Livewire\Component;
use WezomCms\Catalog\Models\Product;
use WezomCms\ProductReviews\Traits\InteractsWithLikes;

/**
 * Class LatestProductReviews
 * @package WezomCms\ProductReviews\Http\Livewire
 * @property Product $product
 */
class LatestProductReviews extends Component
{
    use InteractsWithLikes {
        InteractsWithLikes::like as protected traitLike;
        InteractsWithLikes::dislike as protected traitDislike;
    }

    /**
     * @var int
     */
    public $productId;

    /**
     * @param  Product  $product
     */
    public function mount(Product $product)
    {
        $this->productId = $product->id;

        $this->computedPropertyCache['product'] = $product;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $limit = 3;

        $reviews = $this->product->publishedReviews()
            ->root()
            ->latest()
            ->limit($limit)
            ->get();

        $countReviews = $this->product->published_reviews_count ?? $this->product->publishedReviews()->root()->count();

        return view('cms-product-reviews::site.livewire.latest-product-reviews', [
            'product' => $this->product,
            'countReviews' => $countReviews,
            'reviews' => $reviews,
            'hasMore' => $countReviews > $limit,
        ]);
    }

    /**
     * @param $id
     */
    public function like($id)
    {
        $this->traitLike($id);

        $this->emit('updatedProductReviews');
    }

    /**
     * @param $id
     */
    public function dislike($id)
    {
        $this->traitDislike($id);

        $this->emit('updatedProductReviews');
    }

    /**
     * @return Product
     */
    public function getProductProperty()
    {
        return Product::published()->findOrFail($this->productId);
    }
}
