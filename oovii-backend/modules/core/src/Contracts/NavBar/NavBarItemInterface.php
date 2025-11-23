<?php

namespace WezomCms\Core\Contracts\NavBar;

use Illuminate\Contracts\Support\Htmlable;

interface NavBarItemInterface extends Htmlable
{
    /**
     * @return int
     */
    public function getPosition();
}
