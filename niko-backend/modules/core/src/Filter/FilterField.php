<?php

namespace WezomCms\Core\Filter;

use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use WezomCms\Core\Contracts\Filter\FilterFieldInterface;

class FilterField implements FilterFieldInterface
{
    use FieldGeneratorTrait;
    use Macroable;

    protected $type = self::TYPE_INPUT;

    protected $label;

    protected $placeholder;

    protected $placeholderFrom;

    protected $placeholderTo;

    protected $name;

    protected $size = 3;

    protected $class;

    protected $step = 0.001;

    protected $condition = '=';

    protected $options = [];

    protected $hide = false;

    protected $attributes = [];

    private $customOptions;

    /**
     * Field constructor.
     * @param  array  $params
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $name => $value) {
            $fieldName = camel_case($name);
            if (property_exists($this, $fieldName)) {
                $this->$fieldName = $value;
            }
        }
    }
    /**
     * @param  array  $params
     * @return FilterFieldInterface|FilterField
     */
    public static function make(array $params = [])
    {
        return new static($params);
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     * @return FilterFieldInterface
     */
    public function type(string $type): FilterFieldInterface
    {
        if (
            !in_array(
                $type,
                [
                    static::TYPE_INPUT,
                    static::TYPE_SELECT,
                    static::TYPE_SELECT_WITH_CUSTOM_OPTIONS,
                    static::TYPE_NUMBER,
                    static::TYPE_RANGE,
                    static::TYPE_DATE_RANGE,
                    static::TYPE_DATE_TIME_RANGE,
                ]
            )
        ) {
            throw new InvalidArgumentException('Type [' . $type . '] not allowed');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param  string  $label
     * @return FilterFieldInterface
     */
    public function label(string $label): FilterFieldInterface
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param  string  $placeholder
     * @return FilterFieldInterface
     */
    public function placeholder(string $placeholder): FilterFieldInterface
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder()
    {
        return $this->placeholder ? $this->placeholder : $this->label;
    }

    /**
     * @return mixed
     */
    public function getPlaceholderFrom()
    {
        if ($this->placeholderFrom) {
            return $this->placeholderFrom;
        } elseif ($this->getPlaceholder()) {
            return sprintf('%s (%s)', $this->getPlaceholder(), __('cms-core::admin.filter.From'));
        }

        return null;
    }

    /**
     * @param  string  $placeholderFrom
     * @return FilterFieldInterface
     */
    public function placeholderFrom(string $placeholderFrom): FilterFieldInterface
    {
        $this->placeholderFrom = $placeholderFrom;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlaceholderTo()
    {
        if ($this->placeholderTo) {
            return $this->placeholderTo;
        } elseif ($this->getPlaceholder()) {
            return sprintf('%s (%s)', $this->getPlaceholder(), __('cms-core::admin.filter.To'));
        }

        return null;
    }

    /**
     * @param  string  $placeholderTo
     * @return FilterFieldInterface
     */
    public function placeholderTo(string $placeholderTo): FilterFieldInterface
    {
        $this->placeholderTo = $placeholderTo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     * @return FilterFieldInterface
     */
    public function name(string $name): FilterFieldInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param  int  $colSize
     * @return FilterFieldInterface
     */
    public function size(int $colSize): FilterFieldInterface
    {
        $this->size = $colSize;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param  string  $class
     * @return FilterFieldInterface
     */
    public function class(string $class): FilterFieldInterface
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return float
     */
    public function getStep(): float
    {
        return $this->step;
    }

    /**
     * @param  float  $step
     * @return FilterFieldInterface
     */
    public function step(float $step): FilterFieldInterface
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * @param  string  $condition
     * @return FilterFieldInterface
     */
    public function condition(string $condition): FilterFieldInterface
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     * @return FilterFieldInterface
     */
    public function options(array $options): FilterFieldInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHide(): bool
    {
        return $this->hide;
    }

    /**
     * @param  bool  $hide
     * @return FilterFieldInterface
     */
    public function hide(bool $hide = true): FilterFieldInterface
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = $this->attributes;

        if (!array_key_exists('title', $attributes) && $placeholder = $this->getPlaceholder()) {
            $attributes['title'] = $placeholder;
        }

        return $attributes;
    }

    /**
     * @param  array  $attributes
     * @return FilterFieldInterface
     */
    public function attributes(array $attributes): FilterFieldInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param $options
     * @return FilterFieldInterface
     */
    public function customOptions($options): FilterFieldInterface
    {
        $this->customOptions = $options;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomOptions()
    {
        return $this->customOptions;
    }
}
