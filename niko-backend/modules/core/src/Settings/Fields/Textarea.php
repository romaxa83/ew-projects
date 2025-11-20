<?php

namespace WezomCms\Core\Settings\Fields;

class Textarea extends AbstractField
{
    protected $rows = 5;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_TEXTAREA;
    }

    /**
     * @param  int  $rows
     * @return $this
     */
    public function setRows(int $rows): Textarea
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
