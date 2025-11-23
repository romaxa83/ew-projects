<?php

namespace WezomCms\Orders\Widgets;

use WezomCms\Core\Foundation\Widgets\AbstractWidget;
use WezomCms\Core\Models\Setting;

/**
 * Class ProductText
 * @package WezomCms\Orders\Widgets
 */
class ProductText extends AbstractWidget
{
    /**
     * A list of models that, when changed, will clear the cache of this widget.
     *
     * @var array
     */
    public static $models = [Setting::class];

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $settings = settings('delivery-and-payment.widget', []);

        return [
            'deliveryText' => array_get($settings, 'delivery_text'),
            'paymentText' => array_get($settings, 'payment_text')
        ];
    }
}
