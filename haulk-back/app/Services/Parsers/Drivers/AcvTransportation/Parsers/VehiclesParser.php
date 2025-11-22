<?php


namespace App\Services\Parsers\Drivers\AcvTransportation\Parsers;


use App\Exceptions\Parser\EmptyVehiclesException;
use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{

    private const PATTERN_INTEND = "/^(?<intend>Carrier[^\n]+)Vehicle/m";

    private const PATTERN_FIRST_PART = "/^(?<year>[0-9]{4}) *(?<make>";
    private const PATTERN_SECOND_PART = ") *(?<model>.+?)$/is";

    private string $text;

    private array $vehicles = [];

    private array $result = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this->clearText()
            ->splitVehicles()
            ->getVehicles()
            ->getResult();
    }

    private function clearText(): VehiclesParser
    {
        preg_match(self::PATTERN_INTEND, $this->text, $match);

        $intend = mb_strlen($match['intend'])-2;

        $this->text = (string)preg_replace("/^.{0," . $intend . "}/m", "", $this->text);

        $this->text = (string)preg_replace("/\s*Vehicle\s*/s", "", $this->text);

        return $this;
    }

    /**
     * @return VehiclesParser
     * @throws EmptyVehiclesException
     */
    private function splitVehicles(): VehiclesParser
    {
        $split = preg_split("/(VIN:[^\n]+\n)/is", $this->text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (empty($split[1])) {
            throw new EmptyVehiclesException();
        }

        for ($i = 1, $max = count($split); $i < $max; $i += 2)
        {
            $split[$i] = trim($split[$i]);

            preg_match("/VIN: *(?<vin>[a-z0-9]{17})/is", $split[$i], $match);

            $this->result[]['vin'] = !empty($match['vin']) ? trim($match['vin']) : null;

            $vehicle = preg_replace("/\n/", " ", trim($split[$i-1]));

            $vehicle = preg_replace("/ {2,}/", " ", $vehicle);

            $this->vehicles[] = preg_replace("/\s*Class:\s*.+$/is", "", $vehicle);
        }

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
                $this->result[$i]['year'] = trim($match['year']);
                $this->result[$i]['make'] = trim($match['make']);
                $this->result[$i]['model'] = trim($match['model']);
                continue 2;
            }
            preg_match(self::PATTERN_FIRST_PART . "[^\s]+" . self::PATTERN_SECOND_PART, $this->vehicles[$i], $match);

            if (empty($match)) {
                $this->result[$i]['year'] = null;
                $this->result[$i]['make'] = null;
                $this->result[$i]['model'] = null;
                continue;
            }

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
