<?php

namespace WezomCms\Core\Traits;

use WezomCms\Core\Contracts\BreadcrumbsInterface;

trait BreadcrumbsTrait
{
    /**
     * @param  string|null  $name
     * @param  null  $link
     * @return BreadcrumbsTrait
     */
    public function addBreadcrumb(?string $name, $link = null)
    {
        app(BreadcrumbsInterface::class)->add($name, $link);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBreadcrumbs()
    {
        return app(BreadcrumbsInterface::class)->getBreadcrumbs();
    }
}
