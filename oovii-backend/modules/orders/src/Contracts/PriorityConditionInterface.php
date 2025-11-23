<?php

namespace WezomCms\Orders\Contracts;

interface PriorityConditionInterface
{
    /**
     * @param  int  $priority
     * @return PriorityConditionInterface
     */
    public function setPriority(int $priority): PriorityConditionInterface;

    /**
     * @return int
     */
    public function getPriority(): int;
}
