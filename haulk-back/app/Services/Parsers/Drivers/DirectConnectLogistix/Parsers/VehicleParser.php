<?php


namespace App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers;


use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehicleParser extends ValueParserAbstract
{

    private const PATTERN_FIRST_PART = "/^(?<year>[0-9]{4})? *(?<make>";
    private const PATTERN_SECOND_PART = ")(?: |\/)(?<model>.+?)(?: {5,}?(?<vin>[a-z0-9]+))?$/is";

    private string $text;
    private array $vehiclesDescription = [];
    private array $result = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this->splitVehicle()
            ->getVehicles()
            ->normalizeResult();
    }

    private function splitVehicle(): VehicleParser
    {
        $this->vehiclesDescription = explode("\n", $this->text);

        return $this;
    }

    private function getVehicles(): VehicleParser
    {
        $makes = VehicleMake::get('name')->map(
            function ($item) {
                return preg_quote($item['name'], '/');
            }
        )->toArray();

        foreach ($this->vehiclesDescription as $vehicle) {
            foreach ($makes as $make) {
                if (!preg_match(self::PATTERN_FIRST_PART . $make . self::PATTERN_SECOND_PART, $vehicle, $match)) {
                    continue;
                }
                $this->result[] = $match;
                continue 2;
            }
            preg_match(self::PATTERN_FIRST_PART . "[^\s\/]+" . self::PATTERN_SECOND_PART, $vehicle, $match);
            if (empty($match)) {
                continue;
            }
            $this->result[] = $match;
        }

        return $this;
    }

    private function normalizeResult(): Collection
    {
        foreach ($this->result as &$item) {
            $year = !empty($item['year']) ? $item['year'] : null;
            $make = !empty($item['make']) ? trim($item['make'], " \t\n\r\0\x0B,.!?") : null;
            $model = !empty($item['model']) ? trim($item['model'], " \t\n\r\0\x0B,.!?") : null;
            $vin = !empty($item['vin']) ? trim($item['vin'], " \t\n\r\0\x0B,.!?") : null;

            $item = [
                'year' => $year,
                'make' => $make,
                'model' => $model,
                'vin' => $vin
            ];
        }

        unset($item);

        return collect($this->result);
    }
}
