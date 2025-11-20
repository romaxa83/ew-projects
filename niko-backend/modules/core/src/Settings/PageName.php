<?php

namespace WezomCms\Core\Settings;

use WezomCms\Core\Settings\Fields\Input;

class PageName extends Input
{
    /**
     * AdminLimit constructor.
     * @param  null|RenderSettings  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);
        $this->setName(__('cms-core::admin.settings.Page name'));
        $this->setHelpText(__('cms-core::admin.settings.Might be used in breadcrumbs, sitemap and some other places'));
        $this->setSort(1);
        $this->setIsMultilingual();
        $this->setKey('name');
        $this->setRules('required|string|max:255');
    }
}
