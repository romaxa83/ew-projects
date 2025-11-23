<?php

namespace WezomCms\Core\Listeners;

use Cache;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearWidgetsCache implements ShouldQueue
{
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

        collect(app('widget')->getWidgets())
            ->filter(function ($widgetName) use ($model) {
                return in_array($model, get_class_vars($widgetName)['models']);
            })->when(
                Cache::supportsTags(),
                function ($collection) {
                    Cache::tags($collection->toArray())->flush();
                },
                function () {
                    Cache::flush();
                }
            );
    }
}
