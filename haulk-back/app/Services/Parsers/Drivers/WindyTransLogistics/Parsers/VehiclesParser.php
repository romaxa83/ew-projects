<?php

namespace App\Services\Parsers\Drivers\WindyTransLogistics\Parsers;

use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VehiclesParser extends ValueParserAbstract
{
    private const VEHICLE_PATTERN = "/^(?<year>[0-9]{4})\s*(?<make>%s)\s+(?<model>.+?) (?<vin>[a-z0-9]+)$/i";

    public function parse(string $text): Collection
    {
        $vehicles = explode("\n", trim($this->replaceBefore($text)));
        $makes = VehicleMake::get('name')->map(
            function ($item) {
                return preg_quote($item['name'], '/');
            }
        )->toArray();
        $result = [];
        foreach ($vehicles as $vehicle) {
            foreach ($makes as $make) {
                if (!preg_match(sprintf(self::VEHICLE_PATTERN, $make), $vehicle, $match)) {
                    continue;
                }
                $result[] = [
                    'year' => $match['year'],
                    'make' => $match['make'],
                    'model' => $match['model'],
                    'vin' => preg_match("/[a-z0-9]{17}/i", $match['vin']) ? Str::upper($match['vin']) : null
                ];
                continue 2;
            }
            if (!preg_match(sprintf(self::VEHICLE_PATTERN, ".+?"), $vehicle, $match)) {
                continue;
            }
            $result[] = [
                'year' => $match['year'],
                'make' => $match['make'],
                'model' => $match['model'],
                'vin' => preg_match("/[a-z0-9]{17}/i", $match['vin']) ? Str::upper($match['vin']) : null
            ];
        }
        return collect($result);
    }
}
