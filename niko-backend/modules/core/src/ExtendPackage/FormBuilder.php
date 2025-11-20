<?php

namespace WezomCms\Core\ExtendPackage;

class FormBuilder extends \Collective\Html\FormBuilder
{
    /**
     * Create a form input field.
     *
     * @param  string $type
     * @param  string $name
     * @param  string $value
     * @param  array  $options
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function input($type, $name, $value = null, $options = [])
    {
        if ($type === 'file') {
            $this->addClass($options, 'form-control-file');
        } elseif ($type === 'checkbox' || $type === 'radio') {
            $this->addClass($options, 'form-check-input');
        } elseif (!in_array($type, ['reset', 'submit', 'hidden'])) {
            $this->addClass($options, 'form-control');
        }

        return parent::input($type, $name, $value, $options);
    }

    /**
     * Create a select box field.
     *
     * @param  string $name
     * @param  array  $list
     * @param  string|bool $selected
     * @param  array  $selectAttributes
     * @param  array  $optionsAttributes
     * @param  array  $optgroupsAttributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function select(
        $name,
        $list = [],
        $selected = null,
        array $selectAttributes = [],
        array $optionsAttributes = [],
        array $optgroupsAttributes = []
    ) {
        $this->addClass($selectAttributes, 'form-control');

        return parent::select($name, $list, $selected, $selectAttributes, $optionsAttributes, $optgroupsAttributes);
    }

    /**
     * Create a textarea input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array  $options
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function textarea($name, $value = null, $options = [])
    {
        $this->addClass($options, 'form-control');

        return parent::textarea($name, $value, $options);
    }

    /**
     * Create a status input field.
     *
     * @param  string  $name
     * @param  bool  $checked
     * @param  bool  $default
     * @param  null  $textOn
     * @param  null  $textOff
     * @param  array  $options
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function status($name, $checked = null, $default = true, $textOn = null, $textOff = null, $options = [])
    {
        $this->type = 'checkbox';

        switch ($name) {
            case 'active':
                $textOn = $textOn ? : __('cms-core::admin.layout.Active');
                $textOff = $textOff ? : __('cms-core::admin.layout.Inactive');
                break;
            case 'read':
                $textOn = $textOn ? : __('cms-core::admin.layout.Read');
                $textOff = $textOff ? : __('cms-core::admin.layout.Unread');
                break;
            case 'published':
            default:
                $textOn = $textOn ? : __('cms-core::admin.layout.Yes');
                $textOff = $textOff ? : __('cms-core::admin.layout.No');
        }

        $options = array_merge(
            [
                'data-size' => 'small',
                'id' => str_slug($name),
                'data-toggle' => 'toggle',
                'data-on' => str_replace(' ', '&nbsp;', $textOn),
                'data-off' => str_replace(' ', '&nbsp;', $textOff),
            ],
            $options
        );

        $checked = $this->getCheckboxCheckedState($name, 1, $checked);
        if ($checked || ($checked === null && $default)) {
            $options['checked'] = 'checked';
        }

        $this->addClass($options, 'form-check-input');

        return $this->toHtmlString(
            <<<HTML
<div>
    <input type="hidden" class="js-ignore" name="{$name}" value="0">
    {$this->input($this->type, $name, 1, $options)}
</div>
HTML
        );
    }

    /**
     * @param  array  $options
     * @param  string  $class
     */
    public function addClass(array &$options, string $class)
    {
        $optionClass = array_get($options, 'class', '');

        if (!$optionClass) {
            $options['class'] = $class;
        }

        if (!is_array($optionClass)) {
            $optionClass = explode(' ', $optionClass);
        }

        if (!in_array($class, $optionClass)) {
            $optionClass[] = $class;
            $options['class'] = $optionClass;
        }
    }
}
