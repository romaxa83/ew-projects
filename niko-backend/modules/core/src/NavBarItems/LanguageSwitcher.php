<?php

namespace WezomCms\Core\NavBarItems;

use WezomCms\Core\Foundation\NavBar\AbstractNavBarItem;

class LanguageSwitcher extends AbstractNavBarItem
{
    /**
     * @return mixed
     */
    protected function render()
    {
        $locales = config('cms.core.translations.admin.locales', []);

        if (count($locales) > 1) {
            return view('cms-core::admin.partials.nav-bar-items.language-switcher', compact('locales'));
        } else {
            return '';
        }
    }
}
