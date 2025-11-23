<?php

namespace WezomCms\Core\Settings\Fields;

class Radio extends AbstractField
{
    use ValuesListContainerTrait;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_RADIO;
    }
}
