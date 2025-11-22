<?php

namespace App\Services\Parsers\Drivers\ReadyLogistics\Parsers;

use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{
    private const VEHICLE_PATTERN = "/^(?<year>[0-9]{4})\s*(?<make>%s)\s+(?<model>.*)$/i";

    private string $text;
    private array $vehicles = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this
            ->splitRows()
            ->getVehicles()
            ->getResult();
    }

    private function splitRows(): self
    {
//        $row = preg_replace("/ {2,}/", "--", $this->text);
        $tmp = explode(' ?? ', $this->text);

        $this->vehicles[] = [
            'vehicle' => $tmp[0],
            'vin' => $tmp[1]
        ];

        return $this;
    }

    private function getVehicles(): self
    {
        $makes = VehicleMake::get('name')->map(
            function ($item) {
                return preg_quote($item['name'], '/');
            }
        )->toArray();

        foreach ($this->vehicles as &$vehicle) {
            foreach ($makes as $make) {
                if (!preg_match(sprintf(self::VEHICLE_PATTERN, $make), $vehicle['vehicle'], $match)) {
                    continue;
                }
                $vehicle['make'] = trim($match['make']);
                $vehicle['model'] = !empty($match['model']) ? trim($match['model']) : null;
                $vehicle['year'] = !empty($match['year']) ? trim($match['year']) : null;
                continue 2;
            }
            preg_match(sprintf(self::VEHICLE_PATTERN, "[^\s]+"), $vehicle['vehicle'], $match);

            if (empty($match)) {
                continue;
            }
            $vehicle['make'] = trim($match['make']);
            $vehicle['model'] = !empty($match['model']) ? trim($match['model']) : null;
            $vehicle['year'] = !empty($match['year']) ? trim($match['year']) : null;
        }
        return $this;
    }

    private function getResult(): Collection
    {
        $result = [];
        foreach ($this->vehicles as $vehicle) {
            if (empty($vehicle['make'])) {
                continue;
            }
            $result[] = [
                'year' => !empty($vehicle['year']) ? $vehicle['year'] : null,
                'make' => $vehicle['make'],
                'model' => !empty($vehicle['model']) ? $vehicle['model'] : null,
                'vin' => !empty($vehicle['vin']) && preg_match("/^[a-z0-9]{17}$/i", $vehicle['vin']) ? $vehicle['vin'] : null,
                'color' => !empty($vehicle['color']) ? $vehicle['color'] : null
            ];
        }

        return collect($result);
    }
}
