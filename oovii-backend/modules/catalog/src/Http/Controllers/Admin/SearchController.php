<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use WezomCms\Core\Http\Controllers\SingleSettingsController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MetaFields\Heading;
use WezomCms\Core\Settings\MetaFields\Title;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\PageName;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\SiteLimit;
use WezomCms\Core\Settings\Tab;

class SearchController extends SingleSettingsController
{
    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return 'search';
    }

    /**
     * Page title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-catalog::admin.search.Search');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $search = new RenderSettings(new Tab('search', __('cms-catalog::admin.search.Search'), 2, 'fa-folder-o'));

        return [
            SiteLimit::make($search)->setName(__('cms-catalog::admin.search.Site search limit at page')),
            new MultilingualGroup($search, [PageName::make()->default('Search'), Title::make(), Heading::make()]),
        ];
    }
}
