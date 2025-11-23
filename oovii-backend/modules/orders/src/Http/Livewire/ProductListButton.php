<?php

namespace WezomCms\Orders\Http\Livewire;

use Livewire\Component;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Orders\Contracts\CartInterface;

/**
 * @property-read Product $product
 */
class ProductListButton extends Component
{
    /**
     * @var int
     */
    public $productId;

    /**
     * @param  Product  $product
     */
    public function mount(Product $product)
    {
        $this->productId = $product->getKey();

        $this->computedPropertyCache['product'] = $product;
    }

    /**
     * @return string[]
     */
    protected function getListeners(): array
    {
        return [
            'productAddedToCart:' . $this->productId => '$refresh',
            'cartItemRemoved:' . $this->productId => '$refresh',
        ];
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('cms-orders::site.livewire.product-list-button', [
            'availableForPurchase' => $this->product->availableForPurchase(),
            'inCart' => $this->product->in_cart,
        ]);
    }

    /**
     * @param  CartInterface  $cart
     */
    public function addToCartOrOpenModal(CartInterface $cart)
    {
        $product = $this->product;

        if (!$this->product->in_cart) {
            if (!$product->availableForPurchase()) {
                JsResponse::make()
                    ->success(false)
                    ->notification(__('cms-orders::site.cart.Product cannot be purchased'), 'error')
                    ->emit($this);
                return;
            }

            $cart->add($product->id, $product->minCountForPurchase());

            $this->emit('cartUpdated');

            $this->emitTo('orders.product-button', 'productAddedToCart:' . $product->id);
        }

        JsResponse::make()
            ->modal(['component' => 'orders.cart-popup'])
            ->emit($this);
    }

    /**
     * @return Product
     */
    public function getProductProperty(): Product
    {
        return Product::published()
            ->without(['translation'])
            ->findOrFail($this->productId);
    }
}
