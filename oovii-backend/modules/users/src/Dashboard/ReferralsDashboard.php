<?php

namespace WezomCms\Users\Dashboard;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Users\Models\Inviter;

class ReferralsDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'referrals.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Inviter::query()->whereHas('referrals')->count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-users::admin.Referrals');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-user-plus';
    }

    /**
     * @return string]
     */
    public function iconColorClass(): string
    {
        return 'color-success';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.referrals.index');
    }
}
