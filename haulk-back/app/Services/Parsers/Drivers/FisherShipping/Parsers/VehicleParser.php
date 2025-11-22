<?php


namespace App\Services\Parsers\Drivers\FisherShipping\Parsers;


use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehicleParser extends ValueParserAbstract
{

    private const PATTERN = "/^(?<vin>[a-z0-9]+) {3,}(?<year>[0-9]{4})? {3,}(?<make>.+?) {3,}(?<model>.+?)(?:$| {3,}(?<color>.+$))/im";

    private string $text;

    private array $result = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this->getVehicles()
            ->normalizeResult();
    }

    private function getVehicles(): VehicleParser
    {
        preg_match_all(self::PATTERN, $this->text, $match);

        if (empty($match['vin'])) {
            return $this;
        }

        for ($i = 0, $max = count($match['vin']); $i < $max; $i++) {
            $this->result[] = [
                'year' => trim($match['year'][$i], " \t\n\r\0\x0B,.!?"),
                'make' => trim($match['make'][$i], " \t\n\r\0\x0B,.!?"),
                'model' => trim($match['model'][$i], " \t\n\r\0\x0B,.!?"),
                'vin' => trim($match['vin'][$i], " \t\n\r\0\x0B,.!?"),
                'color' => trim($match['color'][$i], " \t\n\r\0\x0B,.!?")
            ];
        }

        return $this;
    }

    private function normalizeResult(): Collection
    {
        foreach ($this->result as &$item) {
            $item = [
                'year' => !empty($item['year']) ? $item['year'] : null,
                'make' => !empty($item['make']) ? $item['make'] : null,
                'model' => !empty($item['model']) ? $item['model'] : null,
                'vin' => !empty($item['vin']) ? $item['vin'] : null,
                'color' => !empty($item['color']) ? $item['color'] : null
            ];
        }

        unset($item);

        return collect($this->result);
    }
}
