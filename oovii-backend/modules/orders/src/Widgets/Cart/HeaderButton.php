<?php

namespace WezomCms\Orders\Widgets\Cart;

use WezomCms\Core\Foundation\Widgets\AbstractWidget;
use WezomCms\Orders\Contracts\CartInterface;

class HeaderButton extends AbstractWidget
{
    /**
     * View name.
     *
     * @var string
     */
    protected $view = 'cms-orders::site.widgets.cart.header-button';

    /**
     * @param  CartInterface  $cart
     * @return array|null
     */
    public function execute(CartInterface $cart): ?array
    {
        return ['count' => $cart->count()];
    }
}
