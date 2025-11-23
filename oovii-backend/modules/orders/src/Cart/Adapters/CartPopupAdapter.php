<?php

namespace WezomCms\Orders\Cart\Adapters;

use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Contracts\CartAdapterInterface;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemInterface;

class CartPopupAdapter implements CartAdapterInterface
{
    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * @param  CartInterface  $cart
     */
    public function __construct(CartInterface $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Adapt data to concrete template.
     *
     * @return mixed
     */
    public function adapt()
    {
        return [
            'crossedOutSubTotal' => $this->cart->crossedOutSubTotal(),
            'subTotal' => $this->cart->subTotal(),
            'total' => $this->cart->total(),
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
                'product' => $product,
                'url' => $product->getFrontUrl(),
                'src' => $product->getImageUrl(),
                'name' => $product->name,
                'quantity' => [
                    'value' => $cartItem->getQuantity(),
                    'min' => $product->minCountForPurchase(),
                    'step' => $product->stepForPurchase(),
                ],
                'crossed_out_sub_total' => $cartItem->crossedOutSubTotal(),
                'sub_total' => $cartItem->getSubTotal(),
            ];
        })->all();
    }
}
