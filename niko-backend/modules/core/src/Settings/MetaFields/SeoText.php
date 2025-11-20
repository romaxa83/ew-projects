<?php

namespace WezomCms\Core\Settings\MetaFields;

use WezomCms\Core\Settings\Fields\Wysiwyg;
use WezomCms\Core\Settings\RenderSettings;

class SeoText extends Wysiwyg
{
    /**
     * Title constructor.
     * @param  null|RenderSettings  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);
        $this->setIsMultilingual();
        $this->setName(__('cms-core::admin.seo.Seo text'));
        $this->setSort(6);
        $this->setKey('text');
        $this->setRules('nullable|string|max:65535');
    }
}
