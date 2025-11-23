<?php

namespace WezomCms\Users\Dashboard;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Users\Models\User;

class ActiveUsersDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'users.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return User::active()->count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-users::admin.Users active');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-users';
    }

    /**
     * @return string]
     */
    public function iconColorClass(): string
    {
        return 'color-danger';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.users.index', ['active' => 1]);
    }
}
