<?php

namespace WezomCms\Core\Commands;

use WezomCms\Core\RegisterErrorViewPaths;

class DownCommand extends \Illuminate\Foundation\Console\DownCommand
{
    /**
     * Prerender the specified view so that it can be rendered even before loading Composer.
     *
     * @return string
     */
    protected function prerenderView()
    {
        (new RegisterErrorViewPaths())();

        return view($this->option('render'), [
            'retryAfter' => $this->option('retry'),
        ])->render();
    }
}
