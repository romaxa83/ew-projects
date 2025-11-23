<?php

namespace WezomCms\Orders\Contracts;

interface CartAdapterInterface
{
    /**
     * Adapt data to concrete template.
     *
     * @return mixed
     */
    public function adapt();
}
