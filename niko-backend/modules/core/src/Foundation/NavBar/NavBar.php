<?php

namespace WezomCms\Core\Foundation\NavBar;

use Illuminate\Support\Collection;
use WezomCms\Core\Contracts\NavBar\NavBarInterface;
use WezomCms\Core\Contracts\NavBar\NavBarItemInterface;

class NavBar implements NavBarInterface
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new Collection();
    }

    /**
     * @param  NavBarItemInterface  $item
     * @return NavBarInterface
     */
    public function add(NavBarItemInterface $item): NavBarInterface
    {
        $this->collection->push($item);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllItems()
    {
        return $this->collection->sortByDesc(function (NavBarItemInterface $item) {
            return $item->getPosition();
        });
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        $items = $this->collection->map(function (NavBarItemInterface $item) {
            return $item->toHtml();
        });

        return $items->implode('');
    }
}
