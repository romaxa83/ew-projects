<?php

namespace WezomCms\Core\Filter;

use Illuminate\Contracts\Support\Htmlable;
use Request;
use Route;
use Stringable;
use WezomCms\Core\Contracts\Filter\FilterFieldInterface;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Contracts\Filter\RestoreFilterInterface;
use WezomCms\Core\Foundation\Helpers;

class FilterWidget implements Htmlable, Stringable
{
    /**
     * FieldsInterface
     */
    protected $fields;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $values;

    /**
     * Widget constructor.
     * @param  FilterListFieldsInterface  $fields
     * @param  array  $config
     */
    public function __construct(FilterListFieldsInterface $fields, array $config = [])
    {
        $this->fields = $fields;
        $this->config = $config;

        $this->config['control_size'] = method_exists($fields, 'getControlSize')
            ? $fields->getControlSize()
            : FilterListFieldsInterface::CONTROL_SIZE;

        $this->values = Request::all();
    }

    /**
     * @param  FilterListFieldsInterface|string  $fields
     * @param  array  $config
     * @return FilterWidget
     */
    public static function make($fields, array $config = [])
    {
        if (is_string($fields)) {
            $fields = new $fields('');
        }

        return new self($fields, $config);
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function render()
    {
        $fields = $this->fields->getFields();

        if (!count($fields)) {
            return '';
        }

        if (isset($this->values['per_page'])) {
            $fields[] = FilterField::make()->name('per_page')->hide();
        }

        $data = [
            'currentRouteName' => Request::route()->getName(),
            'fields' => $fields,
            'config' => $this->config,
            'values' => $this->values,
            'expanded' => $this->isExpanded($fields),
            'resetUrl' => route(
                Route::currentRouteName(),
                Route::current()->parameters + [RestoreFilterInterface::RESET_FILTER_KEY => 1]
            ),
        ];

        try {
            return view('cms-core::admin.partials.filter', $data)->render();
        } catch (\Throwable $e) {
            report($e);
            if (config('app.debug')) {
                throw $e;
            }

            return '';
        }
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param  iterable|FilterFieldInterface[]  $fields
     * @return boolean
     */
    protected function isExpanded($fields)
    {
        foreach ($fields as $field) {
            if (in_array($field->getType(), [FilterField::TYPE_RANGE, FilterField::TYPE_DATE_RANGE])) {
                if (array_get($this->values, $field->getName() . '_from')) {
                    return true;
                }
                if (array_get($this->values, $field->getName() . '_to')) {
                    return true;
                }
            }

            if (
                trim(array_get($this->values, $field->getName())) !== ''
                && !in_array(
                    $field->getName(),
                    ['page', 'per_page', 'filter_form', RestoreFilterInterface::RESET_FILTER_KEY]
                )
            ) {
                return true;
            }

            $values = array_get(
                $this->values,
                Helpers::convertFieldToDot(preg_replace('/\[\]$/', '', $field->getname()))
            );
            if (is_array($values) && !empty($values)) {
                return true;
            }
        }

        return false;
    }
}
