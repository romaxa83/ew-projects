<?php

namespace WezomCms\Core\Settings\Fields;

class Number extends AbstractField
{
    /**
     * @var int|float
     */
    protected $step = 1;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_NUMBER;
    }

    /**
     * @return float|int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param  float|int  $step
     * @return Number
     */
    public function setStep($step): Number
    {
        $this->step = $step;

        return $this;
    }
}
