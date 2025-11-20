<?php

namespace WezomCms\Core\Settings\MetaFields;

use WezomCms\Core\Settings\Fields\Textarea;
use WezomCms\Core\Settings\RenderSettings;

class Description extends Textarea
{
    /**
     * Description constructor.
     * @param  null|RenderSettings  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);
        $this->setName(__('cms-core::admin.seo.Description'));
        $this->setSort(5);
        $this->setIsMultilingual();
        $this->setKey('description');
        $this->setRules('nullable|string');
    }
}
