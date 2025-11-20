<?php

namespace WezomCms\Core\Foundation\Widgets;

use App;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Application;

class Widget
{
    protected const EMPTY_RESULT = 'empty_result';
    /**
     * @var App
     */
    protected $app;

    /**
     * @var array
     */
    protected $widgets = [];

    /**
     * Widget constructor.
     * @param $app Application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->widgets = config('cms.core.widgets.widgets', []);
    }

    /**
     * @param  string  $name
     * @param  mixed  $class
     * @return $this
     */
    public function register($name, $class)
    {
        $this->widgets[$name] = $class;

        return $this;
    }

    /**
     * @param  string  $name  - Widget name.
     * @param  array  $data
     * @param  string|null  $view
     * @return mixed|string
     * @throws \Throwable
     */
    public function show(string $name, array $data = [], ?string $view = null)
    {
        try {
            $widget = $this->makeWidgetInstance($name, $data, $view);

            if ($widget === null) {
                return '';
            }

            $viewData = $this->execute($widget, $data);

            if ($viewData === null) {
                return '';
            }

            return view($widget->getView(), array_merge($data, $viewData))->render();
        } catch (\Throwable $e) {
            report($e);
            if (config('app.debug')) {
                throw $e;
            }
        }

        return '';
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param $name
     * @return bool
     */
    public function registered($name): bool
    {
        return array_key_exists($name, $this->widgets);
    }

    /**
     * @param  string  $name
     * @param  array  $data
     * @param  string|null  $view
     * @return AbstractWidget|null
     */
    private function makeWidgetInstance(string $name, array $data = [], ?string $view = null): ?AbstractWidget
    {
        if (!isset($this->widgets[$name])) {
            return null;
        }

        $widget = $this->app->make($this->widgets[$name]);

        if (!$widget instanceof AbstractWidget) {
            return null;
        }

        $widget->setData($data);

        if (null !== $view) {
            $widget->setView($view);
        }

        return $widget;
    }

    /**
     * @param  AbstractWidget  $widget
     * @param  array  $data
     * @return array|null
     * @throws \Exception
     */
    private function execute(AbstractWidget $widget, array $data = []): ?array
    {
        if (!method_exists($widget, 'execute')) {
            return [];
        }

        // Create callback
        $callback = function () use ($widget) {
            $result = $this->app->call([$widget, 'execute']);

            return $result === null ? static::EMPTY_RESULT : $result;
        };

        // Cache widget data.
        if ($widget->cacheTime && $this->app->isProduction()) {
            $cacheKey = $this->getCacheKey($widget, $data);

            $data = $this->cacheStorage($widget)->remember($cacheKey, $widget->cacheTime * 60, $callback);
        } elseif (!empty($widget::$models)) {
            $cacheKey = $this->getCacheKey($widget, $data);

            $data = $this->cacheStorage($widget)->rememberForever($cacheKey, $callback);
        } else {
            $data = $callback();
        }

        return $data === static::EMPTY_RESULT ? null : $data;
    }

    /**
     * @param  AbstractWidget  $widget
     * @param  array  $data
     * @return string
     * @throws \Exception
     */
    private function getCacheKey(AbstractWidget $widget, array $data)
    {
        $data['class'] = get_class($widget);
        $data['locale'] = app()->getLocale();
        $data['view'] = $widget->getView();

        sort($data);

        return 'core.widgets.' . serialize($data);
    }


    /**
     * @param  AbstractWidget  $widget
     * @return Repository
     */
    private function cacheStorage(AbstractWidget $widget): Repository
    {
        /** @var Repository $cache */
        $cache = $this->app['cache.store'];

        if (method_exists($cache->getStore(), 'tags')) {
            return $cache->tags(get_class($widget), 'widget');
        }

        return $cache;
    }
}
