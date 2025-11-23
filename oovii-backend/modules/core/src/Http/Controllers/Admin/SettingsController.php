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
     * @return string
     */
    protected function title(): string
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

        switch (config('cms.core.main.map.driver')) {
            case 'google':
                return [
                    Text::make($site)
                        ->setName(__('cms-core::admin.layout.Google maps key'))
                        ->setKey('google_map_key')
                        ->setRules('required|string|max:100')
                ];
            case 'yandex':
                return [
                    Text::make($site)
                        ->setName(__('cms-core::admin.layout.Yandex maps key'))
                        ->setKey('yandex_map_key')
                        ->setRules('required|string|max:100')
                ];
            default:
                return [];
        }
    }
}
