<?php

namespace WezomCms\Core\Contracts\NavBar;

use Illuminate\Contracts\Support\Htmlable;

interface NavBarInterface extends Htmlable
{
    /**
     * @param  NavBarItemInterface  $item
     * @return NavBarInterface
     */
    public function add(NavBarItemInterface $item): NavBarInterface;

    /**
     * @return mixed
     */
    public function getAllItems();
}
