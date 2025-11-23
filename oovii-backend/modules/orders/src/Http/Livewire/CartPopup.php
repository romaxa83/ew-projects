<?php

namespace WezomCms\Orders\Http\Livewire;

use Livewire\Component;
use WezomCms\Orders\Cart\Adapters\CartPopupAdapter;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemInterface;

class CartPopup extends Component
{
    /**
     * @var array
     */
    public $content = [];

    /**
     * @var bool
     */
    public $checkoutPage;

    /**
     * @param  CartInterface  $cart
     * @param  bool  $checkoutPage
     */
    public function mount(CartInterface $cart, bool $checkoutPage = false)
    {
        $this->content = $cart->content()->mapWithKeys(function (CartItemInterface $cartItem) {
            return [$cartItem->getUniqueId() => $cartItem->getQuantity()];
        })->all();

        $this->checkoutPage = $checkoutPage;
    }

    /**
     * @param  CartPopupAdapter  $adapter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render(CartPopupAdapter $adapter)
    {
        return view('cms-orders::site.livewire.cart-popup', $adapter->adapt());
    }

    /**
     * @param  CartInterface  $cart
     * @param  string  $rowId
     */
    public function decreaseCount(CartInterface $cart, string $rowId)
    {
        if ($cartItem = $cart->get($rowId)) {
            $purchaseItem = $cartItem->getPurchaseItem();
            if ($purchaseItem->canDecreaseQuantity($cartItem->getQuantity())) {
                $quantity = $cartItem->getQuantity() - $purchaseItem->stepForPurchase();

                $cartItem->setQuantity($quantity);

                $this->content[$rowId] = $quantity;

                $this->emit('cartUpdated');
            }
        }
    }

    /**
     * @param  CartInterface  $cart
     * @param  string  $rowId
     */
    public function increaseCount(CartInterface $cart, string $rowId)
    {
        if ($cartItem = $cart->get($rowId)) {
            $quantity = $cartItem->getQuantity() + $cartItem->getPurchaseItem()->stepForPurchase();

            $cartItem->setQuantity($quantity);

            $this->content[$rowId] = $quantity;

            $this->emit('cartUpdated');
        }
    }

    /**
     * @param  CartInterface  $cart
     * @param  string  $rowId
     * @param $quantity
     */
    public function setQuantity(CartInterface $cart, string $rowId, $quantity)
    {
        if (($cartItem = $cart->get($rowId)) && $cartItem->getPurchaseItem()->validatePurchaseQuantity($quantity)) {
            $cartItem->setQuantity($quantity);

            $this->content[$rowId] = $quantity;

            $this->emit('cartUpdated');
        }
    }

    /**
     * @param $quantity
     * @param $rowId
     */
    public function updatedContent($quantity, string $rowId)
    {
        $cartItem = app(CartInterface::class)->get($rowId);
        if ($cartItem->getPurchaseItem()->validatePurchaseQuantity($quantity)) {
            $cartItem->setQuantity($quantity);
        } else {
            $this->content[$rowId] = $cartItem->getQuantity();
        }

        $this->emit('cartUpdated');
    }

    /**
     * @param  CartInterface  $cart
     * @param  string  $rowId
     */
    public function removeItem(CartInterface $cart, string $rowId)
    {
        if ($cartItem = $cart->get($rowId)) {
            $cart->remove($rowId);

            unset($this->content[$rowId]);

            $this->emit('cartUpdated');
            $this->emit('cartItemRemoved:' . $cartItem->getId());
        }
    }
}
