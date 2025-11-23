<?php

namespace WezomCms\Catalog\Widgets\Site;

use Request;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class Search extends AbstractWidget
{
    /**
     * Execute method.
     *
     * @return array|void|null
     */
    public function execute(): ?array
    {
        $requestSearch = !is_array(Request::get('search')) ? Request::get('search') : '';

        return compact('requestSearch');
    }
}
