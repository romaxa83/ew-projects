<?php

namespace WezomCms\Core\Foundation\Buttons;

use WezomCms\Core\Contracts\ButtonInterface;
use WezomCms\Core\Contracts\ButtonsContainerInterface;

class ButtonsContainer implements ButtonsContainerInterface
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $items;

    /**
     * ButtonsContainer constructor.
     */
    public function __construct()
    {
        $this->items = collect();
    }

    /**
     * @param  ButtonInterface  $button
     * @return ButtonsContainerInterface
     */
    public function add(ButtonInterface $button): ButtonsContainerInterface
    {
        $this->items->push($button);

        return $this;
    }

    /**
     * Sort buttons
     *
     * @return ButtonsContainerInterface
     */
    public function sort(): ButtonsContainerInterface
    {
        $this->items = $this->items->sortBy(function (ButtonInterface $button) {
            return $button->getSortPosition();
        });

        return $this;
    }

    /**
     * Get all registered buttons
     *
     * @return mixed
     */
    public function get()
    {
        return $this->items;
    }
}
