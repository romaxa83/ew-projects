<?php

namespace WezomCms\Users;

use Config;
use Lavary\Menu\Builder;
use Menu;
use SidebarMenu;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Users\Events\SmsCodeSend;
use WezomCms\Users\Listeners\SmsCodeSendListener;

class UsersServiceProvider extends BaseServiceProvider
{
    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.users.users.dashboards';

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SmsCodeSend::class => [
            SmsCodeSendListener::class,
        ],
    ];


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Application booting.
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Load module config.
     */
    protected function config()
    {
        parent::config();
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('users', __('cms-users::admin.Users'))->withEditSettings();
        $permissions->add('user-cars', __('cms-users::admin.Users cars'))->withEditSettings();
        $permissions->add('loyalties', __('cms-users::admin.loyalty level title'));
    }

    public function adminMenu()
    {
        $users = SidebarMenu::add(__('cms-users::admin.Users'), route('admin.users.index'))
            ->data('icon', 'fa-users')
            ->data('position', 41)
            ->nickname('users');

        $users->add(__('cms-users::admin.Users'), route('admin.users.index'))
            ->data('permission', 'users.view')
            ->data('icon', 'fa-users')
            ->data('position', 1);

        $users->add(__('cms-users::admin.Users cars'), route('admin.user-cars.index'))
            ->data('permission', 'user-cars.view')
            ->data('icon', 'fa-car')
            ->data('position', 2);

        $users->add(__('cms-users::admin.loyalty level title'), route('admin.loyalties.index'))
            ->data('permission', 'loyalties.view')
            ->data('icon', 'fa-list')
            ->data('position', 3);
    }
}
