<?php

namespace WezomCms\Core\Settings\Fields;

class Input extends AbstractField
{
    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_INPUT;
    }
}
