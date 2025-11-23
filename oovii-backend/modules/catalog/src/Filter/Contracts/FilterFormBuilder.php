<?php

namespace WezomCms\Catalog\Filter\Contracts;

interface FilterFormBuilder
{
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_CHECKBOX_WITH_ICON = 'checkbox_with_icon';
    public const TYPE_RADIO = 'radio';
    public const TYPE_RADIO_WITH_ICON = 'radio_with_icon';
    public const TYPE_INPUT = 'input';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_NUMBER_RANGE = 'number_range';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTIPLE_SELECT = 'multiple_select';
    public const TYPE_SELECT_RANGE = 'select_range';
    public const TYPE_HIDDEN = 'hidden';

    /**
     * @param  FilterInterface  $filter
     * @return iterable
     */
    public function buildFormData(FilterInterface $filter): iterable;
}
