<?php

namespace WezomCms\Core\Settings;

use WezomCms\Core\Settings\Fields\Number;

class SiteLimit extends Number
{
    /**
     * AdminLimit constructor.
     * @param  null|RenderSettings  $renderSettings
     * @throws \Exception
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        if ($renderSettings === null) {
            $renderSettings = RenderSettings::siteTab();
        }

        parent::__construct($renderSettings);
        $this->setName(__('cms-core::admin.layout.Number of items displayed on the site'));
        $this->setKey('limit');
        $this->setRules('required|integer|min:1');
        $this->default(10);
    }
}
