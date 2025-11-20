<?php

namespace WezomCms\Core\Foundation\NavBar;

use WezomCms\Core\Contracts\NavBar\NavBarItemInterface;

abstract class AbstractNavBarItem implements NavBarItemInterface
{
    protected $position = 0;

    /**
     * @return mixed
     */
    abstract protected function render();

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
