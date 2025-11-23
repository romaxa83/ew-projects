<?php

namespace WezomCms\Core\Settings\Fields;

class MultiSelect extends AbstractField
{
    use ValuesListContainerTrait;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_MULTI_SELECT;
    }
}
