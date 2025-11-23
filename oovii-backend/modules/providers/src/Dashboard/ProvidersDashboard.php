<?php

namespace WezomCms\Providers\Dashboard;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Providers\Models\Provider;

class ProvidersDashboard extends AbstractValueDashboard
{
    protected $cacheTime = 5;


    protected $ability = 'providers.view';

    public function value(): int
    {
        return Provider::count();
    }

    public function description(): string
    {
        return __('cms-providers::admin.provider.Providers');
    }

    public function icon(): string
    {
        return 'fa-bus';
    }

    public function iconColorClass(): string
    {
        return 'color-success';
    }

    public function url(): ?string
    {
        return route('admin.providers.index');
    }
}

