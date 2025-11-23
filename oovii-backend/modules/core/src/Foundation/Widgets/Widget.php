<?php

namespace WezomCms\Core\Foundation\Widgets;

use App;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class Widget
{
    protected const EMPTY_RESULT = 'empty_result';
    /**
     * @var App
     */
    protected $app;

    /**
     * @var CacheRepository
     */
    protected $cache;

    /**
     * @var array
     */
    protected $widgets = [];

    /**
     * Widget constructor.
     * @param $app Application
     * @param  CacheRepository  $cache
     */
    public function __construct(Application $app, CacheRepository $cache)
    {
        $this->app = $app;

        $this->cache = $cache;

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
        $this->startProfile($name);

        try {
            $widget = $this->makeWidgetInstance($name, $data, $view);

            if ($widget === null) {
                return '';
            }

            $this->startProfile($name . ':execute');
            $viewData = $this->execute($widget, $data);
            $this->stopProfile($name . ':execute');

            if ($viewData === null) {
                return '';
            }

            if ($viewData instanceof HtmlString) {
                return $viewData;
            }

            if (!$view = $widget->getView()) {
                throw new \Exception('Can`t auto build view file name for widget: [' . get_class($widget) . ']');
            }

            $this->startProfile($name . ':render');
            $result = view($view, array_merge($data, $viewData))->render();
            $this->stopProfile($name . ':render');

            return $result;
        } catch (\Throwable $e) {
            report($e);
            if (config('app.debug')) {
                throw $e;
            }
        } finally {
            $this->stopProfile($name);
        }

        return '';
    }

    /**
     * @return array
     */
    public function getWidgets(): array
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
     * @return array|HtmlString|null
     * @throws \Exception
     */
    private function execute(AbstractWidget $widget, array $data = [])
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
        } elseif (!empty($widget::$models) && $this->app->isProduction()) {
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
    protected function getCacheKey(AbstractWidget $widget, array $data)
    {
        $data = $this->serializeWidgetArguments($data);

        $data['class'] = get_class($widget);
        $data['locale'] = app()->getLocale();
        $data['view'] = $widget->getView();

        sort($data);

        return 'core.widgets.' . sha1(json_encode($data));
    }

    /**
     * Serialize widget arguments.
     *
     * @param  array|iterable|Arrayable|Collection  $data
     * @return array
     */
    private function serializeWidgetArguments($data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif (is_iterable($value)) {
                $value = $this->serializeWidgetArguments($value);
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param  AbstractWidget  $widget
     * @return CacheRepository
     */
    protected function cacheStorage(AbstractWidget $widget): CacheRepository
    {
        return $this->cache->supportsTags()
            ? $this->cache->tags([get_class($widget), 'widget'])
            : $this->cache;
    }

    /**
     * @param  string  $name
     */
    protected function startProfile(string $name)
    {
        if (function_exists('start_measure')) {
            start_measure($name);
        }
    }

    /**
     * @param  string  $name
     */
    protected function stopProfile(string $name)
    {
        if (function_exists('stop_measure')) {
            stop_measure($name);
        }
    }
}
