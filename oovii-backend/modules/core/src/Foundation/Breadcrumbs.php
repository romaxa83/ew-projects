<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Support\Collection;
use WezomCms\Core\Contracts\BreadcrumbsInterface;

class Breadcrumbs implements BreadcrumbsInterface
{
    /**
     * @var Collection
     */
    private $list;

    public function __construct()
    {
        $this->list = new Collection();
    }

    /**
     * @param  string|null  $name
     * @param  null  $link
     * @return Breadcrumbs
     */
    public function add(?string $name, $link = null): BreadcrumbsInterface
    {
        if ($name) {
            $this->list->push(compact('name', 'link'));
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getBreadcrumbs(): Collection
    {
        return $this->list->values();
    }
}
