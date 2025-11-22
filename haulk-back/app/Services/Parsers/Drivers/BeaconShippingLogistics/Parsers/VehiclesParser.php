<?php


namespace App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{

    private const PATTERN = "/^V[0-9]+ {3,}(?<year>[0-9]{4}) {3,}(?<make>[a-z0-9\- \'\.]*?[a-z0-9]{2,}[a-z0-9\- \'\.]*?) {3,}(?<model>[a-z0-9\- \'\.]*?[a-z0-9]{2,}[a-z0-9\- \'\.]*?) {3,}(?<vin>[a-z0-9]+) {3,}(?<type>[a-z0-9\- \'\.]*?[a-z0-9]{2,}[a-z0-9\- \'\.]*?)/im";

    private string $text;

    private array $result = [];

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text);

        return $this
            ->getVehicles()
            ->getResult();
    }

    private function getVehicles(): VehiclesParser
    {
        preg_match_all(self::PATTERN, $this->text, $match);

        if (empty($match['make'])) {
            return $this;
        }

        for($i = 0, $max = count($match['make']); $i < $max; $i++) {

            $year = !empty($match['year'][$i]) ? trim($match['year'][$i]) : null;
            $make = !empty($match['make'][$i]) ? trim($match['make'][$i]) : null;
            $model = !empty($match['model'][$i]) ? trim($match['model'][$i]) : null;
            $vin = !empty($match['vin'][$i]) ? trim($match['vin'][$i]) : null;

            $this->result[] = [
                'year' => !empty($year) ? $year : null,
                'make' => !empty($make) ? $make : null,
                'model' => !empty($model) ? $model : null,
                'vin' => !empty($vin) ? $vin : null
            ];
        }
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
