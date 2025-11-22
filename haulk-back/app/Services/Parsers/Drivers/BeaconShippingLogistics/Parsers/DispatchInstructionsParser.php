<?php


namespace App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers;


use App\Services\Parsers\ValueParserAbstract;

class DispatchInstructionsParser extends ValueParserAbstract
{

    private const PATTERN_DISPATCH_INSTRUCTIONS = "/Notes\/Specs:(?<instructions>.*)PICKUP INFORMATION.+/s";
    private const PATTERN_PICKUP_INSTRUCTIONS = "/PICKUP INFORMATION.+\nNotes:(?<instructions>.*)DELIVERY INFORMATION.+/s";
    private const PATTERN_DELIVERY_INSTRUCTIONS = "/PICKUP INFORMATION.+Notes:.*DELIVERY INFORMATION.+\nNotes:(?<instructions>.*)/s";

    private string $text;

    private array $texts = [];

    public function parse(string $text): ?string
    {
        $this->text = $this->replaceBefore($text);

        return $this->setInstructions(self::PATTERN_DISPATCH_INSTRUCTIONS)
            ->setInstructions(self::PATTERN_PICKUP_INSTRUCTIONS)
            ->setInstructions(self::PATTERN_DELIVERY_INSTRUCTIONS)
            ->getResult();
    }

    private function setInstructions(string $pattern): DispatchInstructionsParser
    {
        if (!preg_match($pattern, $this->text, $match)) {
            return $this;
        }

        $instructions = $this->normalizeInstructions($match['instructions']);

        if ($instructions !== null) {
            $this->texts[] = $instructions;
        }

        return $this;
    }

    private function normalizeInstructions(string $instructions): ?string
    {

        $instructions = preg_replace("/\n/", " ", $instructions);

        $instructions = trim(preg_replace("/ {2,}/", " ", $instructions), " \t\n\r\0\x0B,.!?");

        return !empty($instructions) ? $instructions . '.' : null;
    }

    private function getResult(): ?string
    {
        if (empty($this->texts)) {
            return null;
        }

        $this->texts = array_unique($this->texts);

        return implode(" ", $this->texts);
    }
}
