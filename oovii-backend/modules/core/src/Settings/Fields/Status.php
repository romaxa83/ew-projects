<?php

namespace WezomCms\Core\Settings\Fields;

use WezomCms\Core\Settings\RenderSettings;

class Status extends AbstractField
{
    use ValuesListContainerTrait;

    /**
     * @param  RenderSettings|null  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);

        $this->valuesList = [
            '0' => __('cms-core::admin.layout.No'),
            '1' => __('cms-core::admin.layout.Yes')
        ];
    }

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_STATUS;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        $checked = (bool) $this->getValue();

        $attributes = array_merge($this->attributes, [
            'data-size' => 'small',
            'id' => str_slug($this->name),
            'data-toggle' => 'toggle',
            'data-on' => str_replace(' ', '&nbsp;', $this->valuesList[1]),
            'data-off' => str_replace(' ', '&nbsp;', $this->valuesList[0]),
        ]);

        if ($checked) {
            $attributes['checked'] = 'checked';
        }

        return $attributes;
    }
}
