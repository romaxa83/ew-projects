<?php

namespace WezomCms\Core\Settings\MetaFields;

use WezomCms\Core\Settings\Fields\Text;
use WezomCms\Core\Settings\RenderSettings;

class Heading extends Text
{
    /**
     * Heading constructor.
     * @param  null|RenderSettings  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);
        $this->setName(__('cms-core::admin.seo.H1'));
        $this->setSort(2);
        $this->setIsMultilingual();
        $this->setKey('h1');
        $this->setRules('nullable|string|max:255');
    }
}
