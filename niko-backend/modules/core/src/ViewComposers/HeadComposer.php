<?php

namespace WezomCms\Core\ViewComposers;

use Illuminate\Contracts\View\View;
use WezomCms\Core\Contracts\AdminPageNameInterface;

class HeadComposer
{
    /**
     * @var AdminPageNameInterface
     */
    protected $pageName;

    public function __construct(AdminPageNameInterface $pageName)
    {
        $this->pageName = $pageName;
    }

    /**
     * @param  View  $view
     */
    public function compose(View $view)
    {
        $view->with('title', $this->pageName->getPageName() ?? config('app.name'));
    }
}
