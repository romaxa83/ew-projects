<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;

class DispatchInstructionsParser extends ValueParserAbstract
{
    public function parse(string $text): ?string
    {
        $text = trim($text);

        $firstPart = preg_replace('/.*(?=Transport Special Instructions)/s', '', $text);
        $secondPart = preg_replace('/Contract Terms.*$/s', '', $firstPart);

        $lines = explode("\n", $secondPart);

        $result = null;

        foreach ($lines as $k => $line) {
            if($k !== 0){
                if(is_string($line)){
                    $result .= $line . ' ';
                }
            }
        }

        $result = trim(str_replace("\f", " ", $result));

        if($result === "--"){
            return null;
        }

        return trim($result);
    }
}
