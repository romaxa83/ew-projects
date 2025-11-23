<?php

namespace WezomCms\AmoCrm\Http\Controllers\Admin;

use Exception;
use WezomCms\AmoCrm\Services\AmoCrmService;
use WezomCms\Core\Http\Controllers\SingleSettingsController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Select;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;

class AmoCrmController extends SingleSettingsController
{
    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return 'amo-crm';
    }

    /**
     * Page title.
     *
     * @return string|null
     */
    protected function title(): string
    {
        return __('cms-amo-crm::admin.amoCRM');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws Exception
     */
    protected function settings(): array
    {
        return $this->getSettingsFields();
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws Exception
     * @deprecated
     *
     */
    protected function getSettingsFields()
    {

        $settingsTab = new Tab('settings', __('cms-amo-crm::admin.Settings'), 1);
        $settings = new RenderSettings($settingsTab, RenderSettings::SIDE_LEFT);

        // TODO: Display connection info and status
        //$connectionTab = new Tab('connection', __('cms-amo-crm::admin.Connection'), 1);
        //$connectionSettings = new RenderSettings($connectionTab, RenderSettings::SIDE_RIGHT);

        $amoCrmTaskDuePeriods = [
            '' => __('cms-amo-crm::admin.All day'),
            '+5 minutes' => __('cms-amo-crm::admin.:n minutes', ['n' => 5]),
            '+15 minutes' => __('cms-amo-crm::admin.:n minutes', ['n' => 15]),
            '+30 minutes' => __('cms-amo-crm::admin.:n minutes', ['n' => 30]),
        ];

        try {
            $amoCrmUsers = collect(resolve(AmoCrmService::class)->getUsers());
        } catch (Exception $e) {
            $amoCrmUsers = collect();
        }

        $items = [
            Select::make($settings)
                ->setValuesList($amoCrmUsers->pluck('name', 'id')->prepend('', '')->all())
                ->setName(__('cms-amo-crm::admin.Responsible user'))
                ->setKey('responsible_user_id')
                ->setRules('nullable|int|in:'.$amoCrmUsers->pluck('id')->implode(','))
                ->setSort(1),
            Select::make($settings)
                ->setValuesList($amoCrmTaskDuePeriods)
                ->setName(__('cms-amo-crm::admin.Task complete period'))
                ->setKey('complete_till')
                ->setRules('nullable|string|in:'.implode(',', array_keys($amoCrmTaskDuePeriods)))
                ->setSort(2)
        ];

        return $items;
    }
}
