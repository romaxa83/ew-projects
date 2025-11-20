<?php

namespace WezomCms\Core\Foundation\Widgets;

/**
 * Class AbstractWidget
 * @package WezomCms\Core\Foundation\Widgets
 * @method array|null execute() Execute method.
 */
abstract class AbstractWidget
{
    /**
     * The number of minutes before cache expires.
     * False means no caching at all.
     * If debug mode is on - the widget will not be cached.
     *
     * @var int|float|bool
     */
    public $cacheTime = false;

    /**
     * A list of models that, when changed, will clear the cache of this widget.
     *
     * @var array
     */
    public static $models = [];

    /**
     * Widget data
     * @var array
     */
    protected $data = [];

    /**
     * View name.
     *
     * @var string|null
     */
    protected $view = null;

    /**
     * @param  array  $data
     * @return void
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getView(): string
    {
        if ($this->view !== null) {
            return $this->view;
        }

        // Auto build view path
        $namespace = explode('\\', static::class);

        $module = array_get($namespace, 1);
        if ($module) {
            $fileName = str_replace('Widget', '', end($namespace));

            $path = sprintf('cms-%s::site.widgets.%s', snake_case($module, '-'), snake_case($fileName, '-'));

            if (view()->exists($path)) {
                return $path;
            }
        }

        throw new \Exception('Can`t auto build view file name for widget: [' . static::class . ']');
    }

    /**
     * @param $view
     * @return AbstractWidget
     */
    public function setView($view): AbstractWidget
    {
        $this->view = $view;

        return $this;
    }
}
