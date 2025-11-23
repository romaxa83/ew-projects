<?php

namespace WezomCms\Orders\Http\Livewire;

use Livewire\Component;
use WezomCms\Orders\Contracts\CartInterface;

class MobileCartButton extends Component
{
    /**
     * Register component listeners.
     *
     * @var string[]
     */
    protected $listeners = ['cartUpdated' => '$refresh'];

    /**
     * @param  CartInterface  $cart
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render(CartInterface $cart)
    {
        return view('cms-orders::site.livewire.mobile-cart-button', [
            'count' => $cart->count(),
        ]);
    }
}
