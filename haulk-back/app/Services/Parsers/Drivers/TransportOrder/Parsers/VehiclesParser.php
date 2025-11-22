<?php


namespace App\Services\Parsers\Drivers\TransportOrder\Parsers;


use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{

    private const PATTERN_FIRST_PART = "/^(?<vin>[0-9a-z]+) {3,}(?<year>\d{4}) *(?<make>";
    private const PATTERN_SECOND_PART = ") *(?<model>.+?)$/is";

    private string $text;

    private array $vehicles = [];

    private array $result = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this->splitVehicles()
            ->getVehicles()
            ->getResult();
    }

    /**
     * @return VehiclesParser
     */
    private function splitVehicles(): VehiclesParser
    {
        $this->vehicles = explode("\n", $this->text);

        return $this;
    }

    private function getVehicles(): VehiclesParser
    {
        $makes = VehicleMake::get('name')->map(
            function ($item) {
                return preg_quote($item['name'], '/');
            }
        )->toArray();


        for ($i = 0, $max = count($this->vehicles); $i < $max; $i++) {
            foreach ($makes as $make) {
                if (!preg_match(self::PATTERN_FIRST_PART . $make . self::PATTERN_SECOND_PART, $this->vehicles[$i], $match)) {
                    continue;
                }
                $this->result[$i]['vin'] = trim($match['vin']);
                $this->result[$i]['year'] = trim($match['year']);
                $this->result[$i]['make'] = trim($match['make']);
                $this->result[$i]['model'] = trim($match['model']);
                continue 2;
            }

            preg_match(self::PATTERN_FIRST_PART . "[^\s]+" . self::PATTERN_SECOND_PART, $this->vehicles[$i], $match);

            if (empty($match)) {
                $this->result[$i]['vin'] = null;
                $this->result[$i]['year'] = null;
                $this->result[$i]['make'] = null;
                $this->result[$i]['model'] = null;
                continue;
            }

            $this->result[$i]['vin'] = trim($match['vin']);
            $this->result[$i]['year'] = trim($match['year']);
            $this->result[$i]['make'] = trim($match['make']);
            $this->result[$i]['model'] = trim($match['model']);
        }
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
