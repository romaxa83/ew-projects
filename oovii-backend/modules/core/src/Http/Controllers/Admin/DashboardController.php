<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use WezomCms\Core\Foundation\Dashboard\DashboardContainer;
use WezomCms\Core\Http\Controllers\AdminController;

class DashboardController extends AdminController
{
    /**
     * @param  DashboardContainer  $container
     * @return View
     */
    public function index(DashboardContainer $container)
    {
        $this->pageName->setPageName(__('cms-core::admin.layout.Dashboard'));

        return view('cms-core::admin.dashboard.index', ['widgets' => $container->makeWidgets()]);
    }
}
