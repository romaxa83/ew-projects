<?php

namespace WezomCms\Core\ViewComposers;

use Gate;
use Illuminate\Contracts\View\View;

class SidebarComposer
{
    /**
     * @param  View  $view
     */
    public function compose(View $view)
    {
        event('render_admin_menu');

        $sidebarMenu = app('sidebarMenu');

        $sidebarMenu->filter(function ($item) {
            $permissions = array_unique($this->getAllPermissions($item));

            if (!$permissions) {
                return true;
            }

            foreach ($permissions as $item) {
                if (Gate::allows($item)) {
                    return true;
                }
            }

            return false;
        });

        $sidebarMenu->sortBy(function ($elements) {
            // Sort elements by position meta data
            usort($elements, function ($a, $b) {
                return ($a->data('position') ?? 0) <=> ($b->data('position') ?? 0);
            });

            return $elements;
        });

        $view->with('sidebarMenu', $sidebarMenu);
    }

    /**
     * @param $item
     * @param  array  $perms
     * @return array
     */
    private function getAllPermissions($item, array &$perms = []): array
    {
        $perms = array_merge($perms, (array) $item->data('permission'));

        $item->children()->map(function ($children) use (&$perms) {
            $this->getAllPermissions($children, $perms);
        });

        return $perms;
    }
}
