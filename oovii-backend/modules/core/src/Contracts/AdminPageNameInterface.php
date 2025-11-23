<?php

namespace WezomCms\Core\Contracts;

interface AdminPageNameInterface
{
    /**
     * @param  string|null  $name
     * @return $this
     */
    public function setPageName(?string $name);

    /**
     * @return string|null
     */
    public function getPageName();
}
