<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;

class LoadParser extends ValueParserAbstract
{
    public function parse(string $text): ?string
    {
        $text = trim($text);

        $lines = explode("\n", $text);

        $result = null;

        if(isset($lines[0])){
            $loadPart = preg_split('/\s{2,}/', $lines[0]);

            $result = trim(str_replace("Load","", $loadPart[1]));

        }

        return $result;
    }
}
