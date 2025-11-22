<?php


namespace App\Services\Parsers\Drivers\WholesaleExpress2\Parsers;


use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{

    private const PATTERN_CODES_INTEND = "/^(?<intend>.+?\s{6,})CODES:/im";

    private const PATTERN_CLEAR_VEHICLES = [
        ["/^\s*$/m", ""],
        ["/\s*$/m", ""],
        ["/^\n/", ""]
    ];

    private string $text;

    private array $vehicles = [];

    private array $result;

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text, false);

        return $this
            ->removeDamageCodes()
            ->splitVehicles()
            ->setMake()
            ->setModelAndColor()
            ->getResult();
    }

    private function removeDamageCodes(): VehiclesParser
    {
        if (!preg_match(self::PATTERN_CODES_INTEND, $this->text, $match)) {
            return $this;
        }
        $intend = mb_strlen($match['intend']);

        $this->text = (string)preg_replace("/^(.{0," . $intend . "}).*$/m", "$1", $this->text);

        return $this;
    }

    private function splitVehicles(): VehiclesParser
    {
        foreach (self::PATTERN_CLEAR_VEHICLES as $pattern) {
            $this->text = (string)preg_replace($pattern[0], $pattern[1], $this->text);
        }

        preg_match_all("/^(?<vins>(?:[a-z0-9]{11})?[a-z0-9]{6})/im", $this->text, $match);

        $vins = $match['vins'];

        $text = preg_replace("/^.{0,17}/m", "", $this->text);
        $text = preg_replace("/^ */m", "", $text);

        $split = preg_split("/^([0-9]{2} )/m", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $j = 0;

        for ($i = 0, $max = count($split); $i < $max; $i++) {
            $split[$i] = trim($split[$i]);
            if (!$split[$i]) {
                continue;
            }

            $this->result[] = [
                'vin' => $vins[$j],
                'year' => '20' . $split[$i]
            ];

            $vehicle = trim(preg_replace("/\n/", " ", $split[$i+1]), " \t\n\r\0\x0B{}");
            $this->vehicles[] = preg_replace("/([a-z])- ([a-z])/i", "$1$2", $vehicle);
            //Next step plus 2
            $i++;
            //Next VIN
            $j++;
        }
        return $this;
    }

    private function setMake(): VehiclesParser
    {
        $makes = VehicleMake::get('name')->map(
            function ($item) {
                return preg_quote($item['name'], '/');
            }
        )->toArray();
        for ($i = 0, $max = count($this->vehicles); $i < $max; $i++) {
            foreach ($makes as $make) {
                if (!preg_match("/^(?<make>" . $make . ")\s+/i", $this->vehicles[$i], $match)) {
                    continue;
                }
                $this->result[$i]['make'] = trim($match['make']);
                $this->vehicles[$i] = trim(preg_replace("/^" . $match['make'] . "/i", "", $this->vehicles[$i]));
                continue 2;
            }
            preg_match("/^(?<make>[^\s]+)\s+/", $this->vehicles[$i], $match);
            $this->result[$i]['make'] = trim($match['make']);
            $this->vehicles[$i] = trim(preg_replace("/^" . $match['make'] . "/i", "", $this->vehicles[$i]));
        }
        return $this;
    }

    private function setModelAndColor(): VehiclesParser
    {
        for ($i = 0, $max = count($this->vehicles); $i < $max; $i++) {
            if (!preg_match("/(?<color>[a-z\-]+)$/", $this->vehicles[$i], $match)) {
                $this->result[$i]['model'] = $this->vehicles[$i];
                $this->result[$i]['color'] = null;
                continue;
            }
            $this->result[$i]['model'] = trim(preg_replace("/" . $match['color'] . "$/", "", $this->vehicles[$i]));
            $this->result[$i]['color'] = $match['color'];
        }
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
