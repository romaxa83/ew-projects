<?php

namespace WezomCms\Core\Settings\Fields;

class Wysiwyg extends AbstractField
{
    protected $rows = 10;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_WYSIWYG;
    }

    /**
     * @param  int  $rows
     * @return $this
     */
    public function setRows(int $rows): Wysiwyg
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }
}
