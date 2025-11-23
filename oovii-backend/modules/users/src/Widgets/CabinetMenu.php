<?php

namespace WezomCms\Users\Widgets;

use Lavary\Menu\Builder;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class CabinetMenu extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        /** @var Builder $menu */
        $menu = app('cabinetMenu');

        event('render_cabinet_menu', $menu);

        $menu->sortBy(function ($elements) {
            // Sort elements by position meta data
            usort($elements, function ($a, $b) {
                return ($a->data('position') ?? 0) <=> ($b->data('position') ?? 0);
            });

            return $elements;
        });

        return compact('menu');
    }
}
