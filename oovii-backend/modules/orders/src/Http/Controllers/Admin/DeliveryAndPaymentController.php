<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use WezomCms\Core\Http\Controllers\SingleSettingsController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Textarea;
use WezomCms\Core\Settings\Fields\Wysiwyg;
use WezomCms\Core\Settings\MetaFields\SeoFields;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;

class DeliveryAndPaymentController extends SingleSettingsController
{
    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return 'delivery-and-payment';
    }

    /**
     * @return string|null
     */
    protected function frontUrl(): ?string
    {
        return route('delivery-and-payment');
    }

    /**
     * Page title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.delivery-and-payment.Delivery and payment');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $result = [];

        $text1 = Wysiwyg::make()
            ->setIsMultilingual()
            ->setName(__('cms-orders::admin.delivery-and-payment.Text1'))
            ->setKey('text1')
            ->setSort(3);

        $text2 = clone $text1;
        $text2->setName(__('cms-orders::admin.delivery-and-payment.Text2'))
            ->setKey('text2');

        $text3 = clone $text1;
        $text3->setName(__('cms-orders::admin.delivery-and-payment.Text3'))
            ->setKey('text3');

        $result[] = SeoFields::make(
            'Delivery and Payment',
            [$text1, $text2, $text3],
            RenderSettings::siteTab(RenderSettings::SIDE_LEFT)
        );

        $widgetDeliveryText = Textarea::make()
            ->setIsMultilingual()
            ->setName(__('cms-orders::admin.delivery-and-payment.Delivery text'))
            ->setKey('delivery_text')
            ->setSort(1);

        $widgetPaymentText = Textarea::make()
            ->setIsMultilingual()
            ->setName(__('cms-orders::admin.delivery-and-payment.Payment text'))
            ->setKey('payment_text')
            ->setSort(2);

        $result[] = MultilingualGroup::make(
            new RenderSettings(
                new Tab('widget', __('cms-orders::admin.delivery-and-payment.Widget info'), 2, 'fa-info'),
                RenderSettings::SIDE_RIGHT
            ),
            [
                $widgetDeliveryText,
                $widgetPaymentText,
            ]
        );

        return $result;
    }
}
