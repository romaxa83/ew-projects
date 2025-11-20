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
     * @param  string  $pageName
     * @return $this
     */
    public function setPageName(?string $pageName)
    {
        $this->pageName = $pageName;

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
