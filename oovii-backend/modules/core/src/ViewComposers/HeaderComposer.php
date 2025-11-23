<?php

namespace WezomCms\Core\ViewComposers;

use Illuminate\Contracts\View\View;
use WezomCms\Core\Contracts\NavBar\NavBarInterface;

class HeaderComposer
{
    /**
     * @var NavBarInterface
     */
    private $navBar;

    /**
     * HeaderComposer constructor.
     * @param  NavBarInterface  $navBar
     */
    public function __construct(NavBarInterface $navBar)
    {
        $this->navBar = $navBar;
    }

    /**
     * @param  View  $view
     */
    public function compose(View $view)
    {
        $view->with('navBarItems', $this->navBar->getAllItems());

        // Logos
        $wideLogo = config('cms.core.main.logo.wide');
        if (!is_file(public_path($wideLogo))) {
            $wideLogo = null;
        }
        $view->with('wideLogo', $wideLogo);

        $smallLogo = config('cms.core.main.logo.small');
        if (!is_file(public_path($smallLogo))) {
            $smallLogo = null;
        }
        $view->with('smallLogo', $smallLogo);
    }
}
