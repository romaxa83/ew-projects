<?php
namespace App\Utils;

class ColumnDT extends \Yajra\DataTables\Html\Column
{

    /**
     * Parse render attribute.
     * Переназначаем работу с массивом.
     * @param mixed $value
     * @return string|null
     */
    public function parseRender($value)
    {
        /** @var \Illuminate\Contracts\View\Factory $view */
        $view = app('view');
        $parameters = [];

        if (is_array($value)) {
            return $value;
        }

        if (is_callable($value)) {
            return $value($parameters);
        } elseif ($this->isBuiltInRenderFunction($value)) {
            return $value;
        } elseif ($view->exists($value)) {
            return $view->make($value)->with($parameters)->render();
        }
        return $value ? $this->parseRenderAsString($value) : null;
    }
}
