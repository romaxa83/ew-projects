<?php

namespace App\ModelFilters\Saas\GPS\History;

use App\Models\BodyShop\Settings\Settings;
use App\Models\GPS\History;
use Carbon\CarbonImmutable;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class HistoryFilter extends ModelFilter
{
    public function id(int $value): void
    {
        $this->where(History::TABLE_NAME.'.id', $value);
    }

    public function truck(string $value): void
    {
        $this->where('truck_id', $value);
    }

    public function device(string $value): void
    {
        $this->where('device_id', $value);
    }

    public function trailer(string $value): void
    {
        $this->where('trailer_id', $value);
    }

    public function driver(string $value): void
    {
        $this->where('driver_id', $value);
    }

    public function eventType(string $value): void
    {
        $this->where('event_type', $value);
    }

    public function alertType(string $value): void
    {
        $this->whereHas('alerts', function(Builder $b) use ($value) {
            return $b->where('alert_type', $value);
        });
    }

    public function dateFrom(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateFrom = (new CarbonImmutable($date, $timeZone))->startOfDay()->setTimezone('UTC');

        $this->where('received_at', '>=', $dateFrom);
    }

    public function dateTo(string $date): void
    {

        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateTo = (new CarbonImmutable($date, $timeZone))->endOfDay()->setTimezone('UTC');

        $this->where('received_at', '<=', $dateTo);
    }
}

