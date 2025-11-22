<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;

class UpdateDeliveryStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional = [],
    )
    {}

    public function getDetails(): array
    {
        $tmp = [];
        $this->checkFieldsForAdditional(['old_value', 'change_fields']);

        foreach ($this->additional['change_fields'] ?? [] as $field => $value) {
            $frontField = $field;
            if ($field == 'method') $frontField = 'delivery_method';
            if ($field == 'cost') $frontField = 'delivery_cost';
            if ($field == 'sent_at') $frontField = 'date_sent';


            if($field == 'sent_at'){
                $old = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $this->additional['old_value'][$field])->format('Y-m-d');
                $new = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d');
            } else {
                $old = $this->additional['old_value'][$field];
                $new = $value ?? $old;
            }

            $tmp['delivery.'.$this->additional['old_value']['id'].'.'.$frontField] = [
                'old' => $old,
                'new' => $new,
                'type' => self::TYPE_UPDATED
            ];
        }

        return $tmp;
    }
}
