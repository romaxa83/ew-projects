<?php

namespace WezomCms\Core\Contracts;

use Illuminate\Support\Collection;

interface BreadcrumbsInterface
{
    /**
     * @param  string  $name
     * @param  null  $link
     * @return $this
     */
    public function add(string $name, $link = null): BreadcrumbsInterface;

    /**
     * @return Collection
     */
    public function getBreadcrumbs(): Collection;
}
