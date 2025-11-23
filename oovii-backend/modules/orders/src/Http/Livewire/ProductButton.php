<?php

namespace WezomCms\Orders\Http\Livewire;

use Livewire\Component;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Orders\Contracts\CartInterface;

/**
 * @property-read Product $product
 */
class ProductButton extends Component
{
    /**
     * @var int
     */
    public $productId;

    /**
     * @var string|null
     */
    public $buttonClass;

    /**
     * @param  Product  $product
     * @param  string|null  $buttonClass
     */
    public function mount(Product $product, ?string $buttonClass = null)
    {
        $this->productId = $product->getKey();

        $this->computedPropertyCache['product'] = $product;

        $this->buttonClass = $buttonClass;
    }

    /**
     * @return string[]
     */
    protected function getListeners(): array
    {
        return [
            'updateProductId',
            'updateProductCityId',
            'productAddedToCart:' . $this->productId => '$refresh',
            'cartItemRemoved:' . $this->productId => '$refresh',
        ];
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('cms-orders::site.livewire.product-button', [
            'product' => $this->product,
            'inCart' => $this->product->in_cart,
            'availableForPurchase' => $this->availableForPurchase(),
        ]);
    }

    /**
     * @param int $id
     */
    public function updateProductId(int $id)
    {
        $this->productId = $id;
    }

    /**
     * @param  CartInterface  $cart
     */
    public function addToCart(CartInterface $cart)
    {
        $product = $this->product;

        if (!$this->product->in_cart) {
            if (!$this->availableForPurchase()) {
                JsResponse::make()
                    ->success(false)
                    ->notification(__('cms-orders::site.cart.Product cannot be purchased'), 'error')
                    ->emit($this);
                return;
            }

            $cart->add($product->id, $product->minCountForPurchase());

            $this->emitTo('orders.product-button', 'productAddedToCart:' . $product->id);
            $this->emitTo('orders.product-list-button', 'productAddedToCart:' . $product->id);
        }

        $this->emit('cartUpdated');

        JsResponse::make()
            ->modal(['component' => 'orders.cart-popup'])
            ->emit($this);
    }

    /**
     * @return Product
     */
    public function getProductProperty(): Product
    {
        return Product::published()->findOrFail($this->productId);
    }

    /**
     * @return bool
     */
    protected function availableForPurchase(): bool
    {
        return $this->product->availableForPurchase();
    }
}
