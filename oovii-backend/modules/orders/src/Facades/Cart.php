<?php

namespace WezomCms\Orders\Facades;

use Illuminate\Support\Facades\Facade;
use WezomCms\Orders\Contracts\CartInterface;

class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return CartInterface::class;
    }
}
