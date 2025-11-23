<?php

namespace WezomCms\Core\Settings\Fields;

class Text extends AbstractField
{
    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_TEXT;
    }
}
