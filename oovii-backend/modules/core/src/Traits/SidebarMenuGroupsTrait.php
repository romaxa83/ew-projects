<?php

namespace WezomCms\Core\Traits;

use SidebarMenu;

trait SidebarMenuGroupsTrait
{
    /**
     * @return \Lavary\Menu\Item
     */
    protected function serviceGroup()
    {
        $service = SidebarMenu::get('service');
        if (!$service) {
            $service = SidebarMenu::add(__('cms-core::admin.layout.Service'))
                ->data('icon', 'fa-cubes')
                ->data('position', 50)
                ->nickname('service');
        }

        return $service;
    }

    /**
     * @return \Lavary\Menu\Item
     */
    protected function contentGroup()
    {
        $content = SidebarMenu::get('content');
        if (!$content) {
            $content = SidebarMenu::add(__('cms-core::admin.layout.Content'))
                ->data('icon', 'fa-file-text-o')
                ->data('position', 2)
                ->nickname('content');
        }

        return $content;
    }
}
