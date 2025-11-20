<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use WezomCms\Core\Http\Controllers\SingleSettingsController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Text;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\RenderSettings;

class SettingsController extends SingleSettingsController
{
    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return 'settings';
    }

    /**
     * Page title.
     *
     * @return string|null
     */
    protected function title(): ?string
    {
        return __('cms-core::admin.settings.Global settings');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $site = RenderSettings::siteTab();

        return [
            Text::make($site)
                ->setName(__('cms-core::admin.layout.Loyalty link'))
                ->setKey('loyalty_link')
                ->setRules('nullable|string'),
            Text::make($site)
                ->setName(__('cms-core::admin.layout.Privacy policy'))
                ->setKey('privacy_policy_link')
                ->setRules('nullable|string'),
            Text::make($site)
                ->setName(__('cms-core::admin.layout.Terms of use'))
                ->setKey('terms_of_use_link')
                ->setRules('nullable|string'),
        ];
    }
}
