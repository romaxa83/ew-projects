<?php

namespace App\ModelFilters\Saas\GPS\History;

use App\Models\BodyShop\Settings\Settings;
use App\Models\GPS\History;
use Carbon\CarbonImmutable;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class RouteFilter extends ModelFilter
{
    public function truck(string $value): void
    {
        $this->where('truck_id', $value);
    }

    public function trailer(string $value): void
    {
        $this->where('trailer_id', $value);
    }

    public function device(string $value): void
    {
        $this->where('device_id', $value);
    }

    public function date(string $date): void
    {
        $this->where('date',  $date);
    }
}


