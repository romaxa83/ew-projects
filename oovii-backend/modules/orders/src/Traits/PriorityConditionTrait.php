<?php

namespace WezomCms\Orders\Traits;

use WezomCms\Orders\Contracts\PriorityConditionInterface;

trait PriorityConditionTrait
{
    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * @param  int  $priority
     * @return PriorityConditionInterface|static
     */
    public function setPriority(int $priority): PriorityConditionInterface
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
