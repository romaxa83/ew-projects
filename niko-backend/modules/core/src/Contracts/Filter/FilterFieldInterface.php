<?php

namespace WezomCms\Core\Contracts\Filter;

interface FilterFieldInterface
{
    public const TYPE_INPUT = 'input';
    public const TYPE_SELECT = 'select';
    public const TYPE_SELECT_WITH_CUSTOM_OPTIONS = 'select_with_custom_options';
    public const TYPE_NUMBER = 'number';
    public const TYPE_RANGE = 'range';
    public const TYPE_DATE_RANGE = 'date_range';
    public const TYPE_DATE_TIME_RANGE = 'date_time_range';

    /**
     * Field constructor.
     * @param  array  $params
     */
    public function __construct(array $params = []);

    /**
     * @param  array  $params
     * @return FilterFieldInterface
     */
    public static function make(array $params = []);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param  string  $type
     * @return FilterFieldInterface
     */
    public function type(string $type): FilterFieldInterface;

    /**
     * @return mixed
     */
    public function getLabel();

    /**
     * @param  string  $label
     * @return FilterFieldInterface
     */
    public function label(string $label): FilterFieldInterface;

    /**
     * @param  string  $placeholder
     * @return FilterFieldInterface
     */
    public function placeholder(string $placeholder): FilterFieldInterface;

    /**
     * @return mixed
     */
    public function getPlaceholder();

    /**
     * @return mixed
     */
    public function getPlaceholderFrom();

    /**
     * @param  string  $placeholderFrom
     * @return FilterFieldInterface
     */
    public function placeholderFrom(string $placeholderFrom): FilterFieldInterface;

    /**
     * @return mixed
     */
    public function getPlaceholderTo();

    /**
     * @param  string  $placeholderTo
     * @return FilterFieldInterface
     */
    public function placeholderTo(string $placeholderTo): FilterFieldInterface;

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @param  string  $name
     * @return FilterFieldInterface
     */
    public function name(string $name): FilterFieldInterface;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @param  int  $colSize
     * @return FilterFieldInterface
     */
    public function size(int $colSize): FilterFieldInterface;

    /**
     * @return mixed
     */
    public function getClass();

    /**
     * @param  string  $class
     * @return FilterFieldInterface
     */
    public function class(string $class): FilterFieldInterface;

    /**
     * @return float
     */
    public function getStep(): float;

    /**
     * @param  float  $step
     * @return FilterFieldInterface
     */
    public function step(float $step): FilterFieldInterface;

    /**
     * @return string
     */
    public function getCondition(): string;

    /**
     * @param  string  $condition
     * @return FilterFieldInterface
     */
    public function condition(string $condition): FilterFieldInterface;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param $options
     * @return FilterFieldInterface
     */
    public function customOptions($options): FilterFieldInterface;

    /**
     * @return mixed
     */
    public function getCustomOptions();

    /**
     * @param  array  $options
     * @return FilterFieldInterface
     */
    public function options(array $options): FilterFieldInterface;

    /**
     * @return bool
     */
    public function isHide(): bool;

    /**
     * @param  bool  $hide
     * @return FilterFieldInterface
     */
    public function hide(bool $hide = true): FilterFieldInterface;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param  array  $attributes
     * @return FilterFieldInterface
     */
    public function attributes(array $attributes): FilterFieldInterface;
}
