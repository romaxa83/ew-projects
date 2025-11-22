<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class VehiclesParserV2 extends ValueParserAbstract
{
    public function parse(string $text): Collection
    {
        $text = trim($text);

        $firstPart = preg_replace('/.*(?=Vehicle Info)/s', '', $text);
        $secondPart = preg_replace('/Additional Info.*$/s', '', $firstPart);

        $lines = explode("\n", $secondPart);

        $count = 0;
        $tmp = [];
        foreach ($lines as $line) {
            $line = str_replace("\f","", $line);
            if(strpos($line, "Vehicle Year/Make/Model") !== false){
                $count++;
            }
            if($count > 0){
                if($line){
                    $tmp[$count][] = $line;
                }
            }
        }

        $result = [];
        if(!empty($tmp)){
            foreach ($tmp as $item){
                $parts = preg_split('/\s{2,}/', $item[1]);
                $fLine = explode(' ', $parts[0]);

                $year = $fLine[0];
                $make = $fLine[1];
                unset($fLine[0], $fLine[1]);
                $model = implode(' ', $fLine);

                $dKey = null;
                foreach ($item as $k => $i) {
                    if(strpos($i, "Vehicle Type") !== false){
                        $dKey = $k+1;
                    }
                }
                $sLine = preg_split('/\s{2,}/', $item[$dKey]);

                $result[] = [
                    'year' => $year,
                    'make' => $make,
                    'model' => $model,
                    'vin' => isset($parts[1]) && $parts[1] !== '--' ? $parts[1] : null,
                    'color' => isset($sLine[1]) && $sLine[1] !== '--' ? $sLine[1] : null,
                    'license_plate' => isset($sLine[2]) && $sLine[2] !== '--' ? $sLine[2] : null,
                ];
            }
        }

        return collect($result);
    }
}
