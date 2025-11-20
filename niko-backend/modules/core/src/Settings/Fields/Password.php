<?php

namespace WezomCms\Core\Settings\Fields;

class Password extends AbstractField
{
    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_PASSWORD;
    }
}
