<?php

namespace App\Services\Parsers\Drivers\Autosled\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VehiclesParser extends ValueParserAbstract
{
    private const VEHICLE_PATTERN = "/^Year +(?<year>[0-9]{4})\nMake +(?<make>[^\n]+)\nModel +(?<model>[^\n]+)\nType +(?<type>[^\n]+)\nColor +(?<color>[^\n]+)\nVIN # +(?<vin>[^\n]+)\n/is";

    public function parse(string $text): Collection
    {
        $text = $this->replacementIntend($this->replaceBefore($text, false));
        if (!preg_match(self::VEHICLE_PATTERN, $text, $match)) {
            return collect();
        }
        return collect(
            [
                [
                    'year' => $match['year'],
                    'make' => $match['make'],
                    'model' => $match['model'],
                    'type' => $match['type'],
                    'color' => $match['color'] !== 'Not Specified' ? $match['color'] : null,
                    'vin' => preg_match("/^[a-z0-9]{17}$/i", $match['vin']) ? Str::upper($match['vin']) : null
                ]
            ]
        );
    }
}
