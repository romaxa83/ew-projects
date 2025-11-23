<?php

namespace WezomCms\Core\NavBarItems;

use WezomCms\Core\Foundation\NavBar\AbstractNavBarItem;

class Notifications extends AbstractNavBarItem
{
    /**
     * @return mixed
     */
    protected function render()
    {
        $notifications = \Auth::guard('admin')->user()->unreadNotifications;
        $hasUnread = count($notifications) > 0;

        return view('cms-core::admin.partials.nav-bar-items.notifications', compact('notifications', 'hasUnread'));
    }
}
