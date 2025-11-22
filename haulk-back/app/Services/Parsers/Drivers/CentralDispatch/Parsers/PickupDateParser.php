<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;

class PickupDateParser extends ValueParserAbstract
{
    public function parse(string $text): ?string
    {
        $text = trim($text);

        $firstPart = preg_replace('/.*(?=Total Price)/s', '', $text);
        $secondPart = preg_replace('/Payment Terms.*$/s', '', $firstPart);

        $lines = explode("\n", $secondPart);

        $result = null;

        if(isset($lines[2])){
            $result = preg_split('/\s{2,}/', $lines[2]);
        }

        return $result[0] ?? null;
    }
}
