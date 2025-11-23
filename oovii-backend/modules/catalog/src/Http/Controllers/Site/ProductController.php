<?php

namespace WezomCms\Catalog\Http\Controllers\Site;

use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\ViewModels\ProductViewModel;
use WezomCms\Core\Http\Controllers\SiteController;

class ProductController extends SiteController
{
    /**
     * @param $slug
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function __invoke($slug, $id)
    {
        /** @var Product $product */
        $product = Product::with(['category' => published_scope()])->findOrFail($id);

        if (false === $product->published) {
            // If product unpublished - redirect to category or to home
            if ($category = $product->category) {
                return redirect()->to($category->getFrontUrl());
            } else {
                return redirect()->route('home');
            }
        } elseif ($product->slug !== $slug) {
            // Redirect to new slug
            return redirect($product->getFrontUrl(), 301);
        }

        $product->addView();

        return view('cms-catalog::site.product.product', new ProductViewModel($product));
    }
}
