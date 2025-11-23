<?php

namespace WezomCms\Core\Foundation;

use WezomCms\Core\Contracts\AdminPageNameInterface;

class WezomAdminPageName implements AdminPageNameInterface
{
    /**
     * @var string|null
     */
    protected $pageName;

    /**
     * @param  string|null  $name
     * @return $this
     */
    public function setPageName(?string $name)
    {
        $this->pageName = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPageName()
    {
        return $this->pageName;
    }
}
