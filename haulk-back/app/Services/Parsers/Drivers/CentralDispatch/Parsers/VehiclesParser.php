<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParser extends ValueParserAbstract
{
    private const PATTENS_FOR_LINE = [
        [
            'name' => 'year',
            'pattern_grep' => '/^ *([0-9]+ [0-9]{4})/'
        ],
        [
            'name' => 'additional',
            'pattern_grep' => '/Additional Info:/'
        ],
        [
            'name' => 'lot',
            'pattern_grep' => '/Lot *\# *:/'
        ]
    ];

    private const PATTERNS_FOR_REFACTOR_TEXT = [
        [
            'name' => 'make',
            'start' => 0,
            'pattern_split' => "/ ([0-9]+ [0-9]{4} +[a-z]+)/",
            'pattern_intend' => null
        ],
        [
            'name' => 'type',
            'start' => null,
            'pattern_split' => "/ (Type:)/i",
            'pattern_intend' => "/^(?<intend>.*?)Type:/m"
        ],
        [
            'name' => 'color',
            'start' => null,
            'pattern_split' => "/ (Color:)/i",
            'pattern_intend' => "/^(?<intend>.*?)Color:/m"
        ],
        [
            'name' => 'license_plate',
            'start' => null,
            'pattern_split' => "/ (Plate:)/i",
            'pattern_intend' => "/^(?<intend>.*?)Plate:/m"
        ],
        [
            'name' => 'vin',
            'start' => null,
            'pattern_split' => "/ (VIN:)/",
            'pattern_intend' => "/^(?<intend>.*?)VIN:/im"
        ],
        [
            'name' => 'lot',
            'start' => null,
            'pattern_split' => "/ (Lot *\# *:)/i",
            'pattern_intend' => "/^(?<intend>.*?)Lot *\#/m"
        ],
        [
            'name' => 'additional',
            'start' => null,
            'pattern_split' => "/ (Additional Info:)/i",
            'pattern_intend' => "/^(?<intend>.*?)Additional/m"
        ]
    ];

    private const PATTERN_FIRST_PART = "/^[0-9]+ *(?<year>[0-9]{4}) *(?<make>";
    private const PATTERN_SECOND_PART = ") *(?<model>.+?) *Type: *(?<type>.+?)? *Color: *(?<color>.+?)? *Plate: *(?<license_plate>.+?)? *VIN: *(?<vin>[a-z0-9]{17})? *Lot *\#: *(?<lot>.+?)? *Additional *info: *(?<info>.+)?$/is";

    private string $text;
    private array $vehiclesDescription;
    private array $result = [];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this->refactorText()
            ->getVehicles()
            ->normalizeResult();
    }

    private function refactorText(): VehiclesParser
    {
        $vehicles = $this->splitVehicleList();

        $result = [];

        foreach ($vehicles as $vehicle) {
            $intends = $this->getListIntends($vehicle);

            foreach ($intends as $key => $intend) {
                $start = $intend['start'];

                $total = !empty($intends[$key + 1]) ? $intends[$key + 1]['start'] - $start : null;

                $text = trim(
                    preg_replace(
                        "/^.{0," . $start . "}(." . ($total !== null ? "{0," . $total . "}" : "*") . ").*/m",
                        "$1",
                        $vehicle
                    )
                );
                $text = preg_replace("/\n/s", " ", $text);
                $text = preg_replace("/ {2,}/", " ", $text);
                $text = preg_split($intend['pattern_split'], $text, -1, PREG_SPLIT_DELIM_CAPTURE);

                $result[$intend['name']][] = $text[0];

                for ($i = 1; $i < count($text); $i += 2) {
                    $result[$intend['name']][] = $text[$i] . (!empty($text[$i + 1]) ? $text[$i + 1] : '');
                }
            }
        }

        for ($i = 0, $totalVehicle = count($result['make']); $i < $totalVehicle; $i++) {
            $this->vehiclesDescription[] = $result['make'][$i] . ' ' . $result['type'][$i] . ' ' .
                $result['color'][$i] . ' ' . $result['license_plate'][$i] . ' ' . $result['vin'][$i] . ' ' .
                $result['lot'][$i] . ' ' . $result['additional'][$i];
        }
        return $this;
    }

    private function getListIntends(string $vehicleDescription): array
    {
        $result = [];
        foreach (self::PATTERNS_FOR_REFACTOR_TEXT as $pattern) {
            if ($pattern['pattern_intend'] === null) {
                $result[] = $pattern;
                continue;
            }

            preg_match_all($pattern['pattern_intend'], $vehicleDescription, $match);
            $intends = [];

            foreach ($match['intend'] as $item) {
                $intends[] = mb_strlen($item);
            }
            if(!empty($intends)){
                $pattern['start'] = min($intends);
            }


            $result[] = $pattern;
        }
        return $result;
    }

    private function getVehicles(): VehiclesParser
    {

//        dd('f');

        $makes = VehicleMake::get('name')->map(
            function ($item) {
                return preg_quote($item['name'], '/');
            }
        )->toArray();

        foreach ($this->vehiclesDescription as $description) {
            foreach ($makes as $make) {
                if (!preg_match(self::PATTERN_FIRST_PART . $make . self::PATTERN_SECOND_PART, $description, $match)) {
                    continue;
                }
                $this->result[] = $match;
                continue 2;
            }
            preg_match(self::PATTERN_FIRST_PART . "[^\s]+" . self::PATTERN_SECOND_PART, $description, $match);
            if (empty($match)) {
                continue;
            }
            $this->result[] = $match;
        }
        return $this;
    }

    private function normalizeResult(): Collection
    {
        $result = [];

        foreach ($this->result as $item) {
            $result[] = [
                'year' => !empty($item['year']) ? trim($item['year']) : null,
                'make' => !empty($item['make']) ? trim($item['make']) : null,
                'model' => !empty($item['model']) ? trim($item['model']) : null,
                'color' => !empty($item['color']) ? trim($item['color']) : null,
                'license_plate' => !empty($item['license_plate']) ? trim($item['license_plate']) : null,
                'vin' => !empty($item['vin']) ? $item['vin'] : null
            ];
        }
        return collect($result);
    }

    private function splitVehicleList(): array
    {
        $text = explode("\n", $this->text);

        $lines = [];

        foreach (self::PATTENS_FOR_LINE as $item) {
            $keys = array_keys(preg_grep($item['pattern_grep'], $text));
            for ($i = 0; $i < count($keys); $i++) {
                $lines[$i] = !array_key_exists($i, $lines) || $lines[$i] > $keys[$i] ? $keys[$i] : $lines[$i];
            }
        }

        $result = [];

        for ($i = 0, $max = count($lines); $i < $max; $i++) {
            $result[] = implode(
                "\n",
                array_slice(
                    $text,
                    $lines[$i],
                    !empty($lines[$i + 1]) ? $lines[$i + 1] - $lines[$i] : null
                )
            );
        }

        return $result;
    }
}
