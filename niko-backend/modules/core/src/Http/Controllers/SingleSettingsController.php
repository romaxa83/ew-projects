<?php

namespace WezomCms\Core\Http\Controllers;

use WezomCms\Core\Traits\SettingControllerTrait;

abstract class SingleSettingsController extends AdminController
{
    use SettingControllerTrait;

    protected function before()
    {
        $this->addBreadcrumb($this->title());
    }

    /**
     * Page title.
     *
     * @return string|null
     */
    abstract protected function title(): ?string;

    /**
     * @param $baseRouteName
     * @param  array  $parameters
     * @return string
     */
    protected function listRoute($baseRouteName, array $parameters = [])
    {
        return route('admin.dashboard', $parameters);
    }

    /**
     * @return string
     */
    protected function indexRoute()
    {
        return route('admin.dashboard');
    }
}
