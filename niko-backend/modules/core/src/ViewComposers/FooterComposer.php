<?php

namespace WezomCms\Core\ViewComposers;

use Illuminate\Contracts\View\View;

class FooterComposer
{
    /**
     * @param  View  $view
     */
    public function compose(View $view)
    {
        $view->with('version', app('config')->get('cms.core.main.version'));
        $view->with('vendor', app('config')->get('cms.core.main.vendor'));
    }
}
