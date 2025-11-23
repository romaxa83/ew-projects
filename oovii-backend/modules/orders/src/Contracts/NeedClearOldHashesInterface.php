<?php

namespace WezomCms\Orders\Contracts;

interface NeedClearOldHashesInterface
{
    /**
     * @return bool
     */
    public function clearOldHashes(): bool;
}
