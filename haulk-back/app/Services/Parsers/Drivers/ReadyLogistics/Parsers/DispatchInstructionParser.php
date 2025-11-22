<?php

namespace App\Services\Parsers\Drivers\ReadyLogistics\Parsers;

use App\Services\Parsers\ValueParserAbstract;

class DispatchInstructionParser extends ValueParserAbstract
{
    public function parse(string $text)
    {
        $this->text = trim($text);

        $firstPart = trim($text);
        $firstPart = preg_replace('/(.+Pre-Dispatch Notes)/s', '', $firstPart);
        $firstPart = preg_replace('/(Transport Release Notes.*)/s', '', $firstPart);
        $firstPart = preg_replace('/\n/', '', $firstPart);

        $secondPart = trim($text);
        $secondPart = preg_replace('/(.+Additional Info)/s', '', $secondPart);
        $secondPart = preg_replace('/(Dispatch Sheet.*)/s', '', $secondPart);

        if($firstPart != null && $firstPart != '--'){
            $secondPart = $firstPart . $secondPart;
        }

        return $secondPart;
    }
}
