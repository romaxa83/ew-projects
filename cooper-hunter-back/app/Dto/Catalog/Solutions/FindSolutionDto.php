<?php

namespace App\Dto\Catalog\Solutions;

use App\Enums\Solutions\SolutionZoneEnum;
use Illuminate\Support\Collection;

class FindSolutionDto
{

    private string $zone;

    private int $countZones;

    private array $climateZones;

    private int $seriesId;

    private int $btu;

    private int $voltage;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->zone = $args['zone'];
        $dto->countZones = (int)data_get($args, 'count_zones', 1);
        $dto->climateZones = $args['climate_zones'];
        $dto->seriesId = (int)$args['series_id'];
        $dto->btu = (int)$args['btu'];
        $dto->voltage = (int)data_get($args, 'voltage', config('catalog.solutions.voltage.default'));

        return $dto;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function isSingleZone(): bool
    {
        return $this->zone === SolutionZoneEnum::SINGLE;
    }

    public function getCountZones(): int
    {
        return $this->countZones;
    }

    public function getClimateZones(): Collection
    {
        return collect($this->climateZones);
    }

    public function getSeriesId(): int
    {
        return $this->seriesId;
    }

    public function getBtu(): int
    {
        return $this->btu;
    }

    public function getVoltage(): int
    {
        return $this->voltage;
    }
}


