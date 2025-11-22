<?php

namespace Tests\Builders\Saas\GPS;

use App\Enums\Format\DateTimeEnum;
use App\Models\GPS\Route;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class RouteBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Route::class;
    }

    public function truck(Truck $model): self
    {
        $this->data['truck_id'] = $model->id;
        return $this;
    }

    public function data(array $value): self
    {
        $this->data['data'] = $value;
        return $this;
    }

    public function date(CarbonImmutable $value): self
    {
        $this->data['date'] = $value->format(DateTimeEnum::DATE_FRONT);
        return $this;
    }
}
