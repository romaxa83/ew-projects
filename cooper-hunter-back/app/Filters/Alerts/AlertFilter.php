<?php


namespace App\Filters\Alerts;


use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;

class AlertFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function objectName(array $objectName): void
    {
        $this->whereIn('model_type', $objectName);
    }
}
