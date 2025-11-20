<?php

namespace WezomCms\Core\Settings\MetaFields;

use WezomCms\Core\Settings\Fields\Text;
use WezomCms\Core\Settings\RenderSettings;

class Title extends Text
{
    /**
     * Title constructor.
     * @param  null|RenderSettings  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);
        $this->setName(__('cms-core::admin.seo.Title'));
        $this->setSort(1);
        $this->setIsMultilingual();
        $this->setKey('title');
        $this->setRules('nullable|string|max:255');
    }
}
