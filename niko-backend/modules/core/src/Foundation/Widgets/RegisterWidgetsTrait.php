<?php

namespace WezomCms\Core\Foundation\Widgets;

trait RegisterWidgetsTrait
{
    /**
     * Register all widgets
     * @param  string|array  $widgets  - associative array with widget aliases or array with config pathes or config path.
     */
    public function registerWidgets($widgets)
    {
        /** @var Widget $widget */
        $widget = app('widget');

        foreach ((array) $widgets as $key => $item) {
            $items = is_array($item) ? $item : config($item, []);

            if (!$items && is_string($key)) {
                $items = [$key => $item];
            }

            foreach ($items as $name => $class) {
                $widget->register($name, $class);
            }
        }
    }
}
