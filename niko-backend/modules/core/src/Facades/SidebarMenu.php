<?php

namespace WezomCms\Core\Facades;

use Illuminate\Support\Facades\Facade;

class SidebarMenu extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sidebarMenu';
    }
}
