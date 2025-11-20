<?php

namespace WezomCms\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use WezomCms\Core\Foundation\Widgets\Widget;

class ClearWidgetsCache implements ShouldQueue
{
    /**
     * @var Widget
     */
    protected $widget;

    /**
     * ClearWidgetsCache constructor.
     */
    public function __construct()
    {
        $this->widget = app('widget');
    }

    /**
     * @param  string  $event
     */
    public function handle(string $event)
    {
        preg_match('/:\s(.*)$/', $event, $matches);
        if (count($matches) === 0) {
            return;
        }

        $model = $matches[1];

        collect($this->widget->getWidgets())
            ->filter(function ($widgetName) use ($model) {
                return in_array($model, get_class_vars($widgetName)['models']);
            })->each(function ($widgetName) {
                $cache = app('cache.store');

                // If tagged cache - flush only by widget class tag
                if (method_exists($cache->getStore(), 'tags')) {
                    $cache->tags($widgetName)->flush();
                } else {
                    // Flush all cache and break
                    $cache->flush();

                    return false;
                }
            });
    }
}
