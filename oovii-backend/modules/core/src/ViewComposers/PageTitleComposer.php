<?php

namespace WezomCms\Core\ViewComposers;

use Illuminate\Contracts\View\View;
use WezomCms\Core\Contracts\AdminPageNameInterface;
use WezomCms\Core\Contracts\BreadcrumbsInterface;

class PageTitleComposer
{
    /**
     * @var AdminPageNameInterface
     */
    private $pageName;

    /**
     * @var BreadcrumbsInterface
     */
    private $breadcrumbs;

    /**
     * BreadcrumbsComposer constructor.
     * @param  AdminPageNameInterface  $pageName
     * @param  BreadcrumbsInterface  $breadcrumbs
     */
    public function __construct(AdminPageNameInterface $pageName, BreadcrumbsInterface $breadcrumbs)
    {
        $this->pageName = $pageName;
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @param  View  $view
     */
    public function compose(View $view)
    {
        $breadcrumbs = $this->breadcrumbs->getBreadcrumbs();

        $view->with('heading', $this->pageName->getPageName());
        $view->with('breadcrumbs', $breadcrumbs->count() > 1 ? $breadcrumbs->pluck('name')->join(' -> ') : null);
    }
}
