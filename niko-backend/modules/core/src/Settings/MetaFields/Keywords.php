<?php

namespace WezomCms\Core\Settings\MetaFields;

use WezomCms\Core\Settings\Fields\Textarea;
use WezomCms\Core\Settings\RenderSettings;

class Keywords extends Textarea
{
    /**
     * Keywords constructor.
     * @param  null|RenderSettings  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);
        $this->setName(__('cms-core::admin.seo.Keywords'));
        $this->setSort(4);
        $this->setIsMultilingual();
        $this->setKey('keywords');
        $this->setRules('nullable|string');
    }
}
