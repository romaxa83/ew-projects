<?php

namespace WezomCms\Core\Settings\Fields;

class File extends AbstractField
{
    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_FILE;
    }

    /**
     * @return bool
     */
    public function isAttachment(): bool
    {
        return true;
    }
}
