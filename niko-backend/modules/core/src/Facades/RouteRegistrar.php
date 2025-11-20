<?php

namespace WezomCms\Core\Facades;

use Illuminate\Support\Facades\Facade;

class RouteRegistrar extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \WezomCms\Core\Foundation\RouteRegistrar::class;
    }
}
