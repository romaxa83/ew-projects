<?php

namespace WezomCms\Supports\Dashboard;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Promotions\Models\Promotions;
use WezomCms\Supports\Models\Support;

class SupportDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'supports.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Support::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-supports::admin.Support');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-cog';
    }

    /**
     * @return string]
     */
    public function iconColorClass(): string
    {
        return 'color-info';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.supports.index');
    }
}



