<?php

namespace WezomCms\Orders\Cart\Adapters;

use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Contracts\CartAdapterInterface;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemInterface;

class SmallCartItemsAdapter implements CartAdapterInterface
{
    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * JsonAdapter constructor.
     * @param  CartInterface  $cart
     */
    public function __construct(CartInterface $cart)
    {
        $this->cart = $cart;
    }


    /**
     * Adapt data to concrete template.
     *
     * @return array
     */
    public function adapt(): array
    {
        return [
            'subTotal' => $this->cart->subTotal(),
            'count' => $this->cart->count(),
            'items' => $this->getItems(),
        ];
    }

    /**
     * @return array
     */
    protected function getItems(): array
    {
        return $this->cart->content()->map(function (CartItemInterface $cartItem) {
            /** @var Product $product */
            $product = $cartItem->getPurchaseItem();

            return [
                'row_id' => $cartItem->getUniqueId(),
                'url' => $product->getFrontUrl(),
                'image' => $product->getImageUrl(),
                'name' => $product->name,
                'quantity' => $cartItem->getQuantity(),
                'crossed_out_sub_total' => $cartItem->crossedOutSubTotal(),
                'sub_total' => $cartItem->getSubTotal(),
            ];
        })->all();
    }
}
