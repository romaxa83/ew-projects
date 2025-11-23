<?php

namespace WezomCms\Core\Contracts;

interface ButtonsContainerInterface
{
    /**
     * @param  ButtonInterface  $button
     * @return ButtonsContainerInterface
     */
    public function add(ButtonInterface $button): ButtonsContainerInterface;

    /**
     * Sort buttons
     *
     * @return ButtonsContainerInterface
     */
    public function sort(): ButtonsContainerInterface;

    /**
     * Get all registered buttons
     *
     * @return mixed
     */
    public function get();
}
