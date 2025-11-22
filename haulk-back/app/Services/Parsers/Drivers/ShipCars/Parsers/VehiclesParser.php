<?php

namespace App\Services\Parsers\Drivers\ShipCars\Parsers;

use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VehiclesParser extends ValueParserAbstract
{
    private const VEHICLE_PATTERN = "/^(?<year>[0-9]{4})\s*(?<make>%s)\s+(?<model>.*)$/";
    private const INTENDS = [
        [
            'name' => 'vehicle',
            'pattern_intend' => "/^(?<intend>.*?)Vehicle/s"
        ],
        [
            'name' => 'vin',
            'pattern_intend' => "/^(?<intend>.+?)VIN/s"
        ],
        [
            'name' => 'type',
            'pattern_intend' => "/^(?<intend>.+?)Type/s"
        ],
        [
            'name' => 'operable',
            'pattern_intend' => "/^(?<intend>.+?)Operable/s"
        ],
        [
            'name' => 'color',
            'pattern_intend' => "/^(?<intend>.+?)Color/s"
        ],
        [
            'name' => 'buyer',
            'pattern_intend' => "/^(?<intend>.+?)Buyer/s"
        ],
        [
            'name' => 'lot',
            'pattern_intend' => "/^(?<intend>.+?)Lot/s"
        ]
    ];

    private string $text;
    private array $vehicles = [];
    private array $intends = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this
            ->calculateIntends()
            ->splitRows()
            ->splitColumns()
            ->getVehicles()
            ->getResult();
    }

    private function calculateIntends(): self
    {
        foreach (self::INTENDS as $intend) {
            preg_match_all($intend['pattern_intend'], $this->text, $match);
            $intends = [];

            foreach ($match['intend'] as $item) {
                $intends[] = mb_strlen($item);
            }

            $this->intends[] = [
                'name' => $intend['name'],
                'value' => min($intends)
            ];
        }

        return $this;
    }

    private function splitRows(): self
    {
        $vehicles = preg_split("/\n([0-9]{4})/", preg_replace("/^.+?\n/", "", $this->text), -1, PREG_SPLIT_DELIM_CAPTURE);
        $this->vehicles[] = $vehicles[0];
        for ($i = 1; $i < count($vehicles); $i += 2) {
            $this->vehicles[] = $vehicles[$i] . $vehicles[$i + 1];
        }
        return $this;
    }

    private function splitColumns(): self
    {
        foreach ($this->vehicles as &$vehicle) {
            $_vehicle = [];
            foreach ($this->intends as $key => $intend) {
                $value = trim($vehicle);
                if (!empty($this->intends[$key+1])) {
                    $value = preg_replace("/^(.{0," . $this->intends[$key+1]['value'] . "}).*/m", "$1", $value);
                }
                $value = preg_replace("/^.{0," . $intend['value'] . "}/m", "", $value);
                $value = preg_replace("/\n/", " ", $value);
                $value = preg_replace("/ {2,}/", " ", $value);
                $_vehicle[$intend['name']] = trim($value);
            }
            $vehicle = $_vehicle;
        }
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
                'vin' => !empty($vehicle['vin']) && preg_match("/^[a-z0-9]{17}$/i", $vehicle['vin']) ? Str::upper($vehicle['vin']) : null,
                'color' => !empty($vehicle['color']) ? $vehicle['color'] : null
            ];
        }
        return collect($result);
    }
}
