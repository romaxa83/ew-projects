<?php


namespace App\Services\Parsers\Drivers\WholesaleExpress\Parsers;


use App\Exceptions\Parser\EmptyVehiclesException;
use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{

    private const PATTERN_VINS = "/^(?<vins>[a-z0-9]{17})\s*/im";

    private string $text;

    private array $VINs = [];

    private array $vehicles = [];

    private array $result;

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text);

        return $this
            ->getVINs()
            ->splitVehicles()
            ->setMake()
            ->setModelAndColor()
            ->getResult();
    }

    /**
     * @return VehiclesParser
     * @throws EmptyVehiclesException
     */
    private function getVINs(): VehiclesParser
    {
        preg_match_all(self::PATTERN_VINS, $this->text, $match);

        if (empty($match['vins'])) {
            throw new EmptyVehiclesException();
        }

        $this->VINs = array_map(
            function (string $item) {
                return mb_strtoupper(trim($item));
            },
            $match['vins']
        );

        $this->text = (string)preg_replace(self::PATTERN_VINS, "", $this->text);

        return $this;
    }

    private function splitVehicles(): VehiclesParser
    {
        $split = preg_split("/^\s*([0-9]{4} )/m", $this->text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $j = 0;

        for ($i = 0, $max = count($split); $i < $max; $i++) {
            $split[$i] = trim($split[$i]);
            if (!$split[$i]) {
                continue;
            }

            $this->result[] = [
                'vin' => $this->VINs[$j],
                'year' => $split[$i]
            ];

            $this->vehicles[] = trim(preg_replace("/\n/", " ", $split[$i+1]), " \t\n\r\0\x0B{}");
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
