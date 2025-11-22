<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;

class ChangeStatusStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    public function getDetails(): array
    {
        $this->checkFieldsForAdditional([
            'old_status',
            'data'
        ]);

        $tmp['status'] = [
            'old' => $this->additional['old_status'],
            'new' => $this->model->status->value,
            'type' => self::TYPE_UPDATED
        ];

        if(
            isset($this->additional['data']['status'])
            && $this->additional['data']['status'] == OrderStatus::Sent()
        ){
            foreach ($this->model->deliveries as $delivery){
                $tmp['delivery.'.$delivery->id.'.delivery_method'] = [
                    'old' => null,
                    'new' => $delivery->method->value,
                    'type' => self::TYPE_ADDED
                ];
                $tmp['delivery.'.$delivery->id.'.delivery_cost'] = [
                    'old' => null,
                    'new' => $delivery->cost,
                    'type' => self::TYPE_ADDED
                ];
                $tmp['delivery.'.$delivery->id.'.date_sent'] = [
                    'old' => null,
                    'new' => $delivery->sent_at->format('Y-m-d'),
                    'type' => self::TYPE_ADDED
                ];
                $tmp['delivery.'.$delivery->id.'.status'] = [
                    'old' => null,
                    'new' => $delivery->status->value,
                    'type' => self::TYPE_ADDED
                ];
                if($delivery->tracking_number){
                    $tmp['delivery.'.$delivery->id.'.tracking_number'] = [
                        'old' => null,
                        'new' => $delivery->tracking_number,
                        'type' => self::TYPE_ADDED
                    ];
                }
            }
        }

        return $tmp;
    }
}
