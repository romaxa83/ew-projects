<?php

namespace WezomCms\Firebase\Templates\Strategies;

use WezomCms\Firebase\Templates\TemplateStrategyParse;
use WezomCms\Orders\Models\Order;

class OrdersStatusChangedStrategy extends AbstractStrategy implements TemplateStrategyParse
{
    protected function setVars(): void
    {
        $this->vars['order_id'] = $this->getOrderId();
        $this->vars['user_name'] = $this->getUserName();
        $this->vars['status_text'] = $this->getStatusText();
    }

    private function getUserName(): string
    {
        return $this->getUserModel()->name ?? '';
    }

    private function getOrderId(): string
    {
        $order = $this->getOrderModel();

        return $order ? $order->getKey() : '';
    }

    private function getStatusText(): string
    {
        $order = $this->getOrderModel();

        return $order && $order->status ? $order->status->notification_text : '';
    }

    private function getOrderModel(): null|Order
    {
        foreach ($this->models as $model) {
            if($model instanceof Order){
                return $model;
            }
        }

        return null;
    }
}
