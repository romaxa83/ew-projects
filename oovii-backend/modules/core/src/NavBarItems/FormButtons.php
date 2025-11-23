<?php

namespace WezomCms\Core\NavBarItems;

use WezomCms\Core\Foundation\NavBar\AbstractNavBarItem;

class FormButtons extends AbstractNavBarItem
{
    protected $position = 20;

    /**
     * @return mixed
     */
    protected function render()
    {
        return view('cms-core::admin.partials.nav-bar-items.form-buttons');
    }
}
