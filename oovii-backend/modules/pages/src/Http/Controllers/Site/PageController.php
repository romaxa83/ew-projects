<?php

namespace WezomCms\Pages\Http\Controllers\Site;

use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Pages\Models\Page;

class PageController extends SiteController
{
    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function __invoke($slug)
    {
        /** @var Page $obj */
        $obj = Page::publishedWithSlug($slug)->firstOrFail();

        $this->setLangSwitchers($obj, 'page.inner');

        // Breadcrumbs
        $this->addBreadcrumb($obj->name, $obj->getFrontUrl());

        $this->seo()->fill($obj, false);

        // Render
        return view('cms-pages::site.index', compact('obj'));
    }
}
