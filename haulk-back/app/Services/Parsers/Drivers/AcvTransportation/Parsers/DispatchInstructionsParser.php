<?php


namespace App\Services\Parsers\Drivers\AcvTransportation\Parsers;


use App\Services\Parsers\ValueParserAbstract;

class DispatchInstructionsParser extends ValueParserAbstract
{

    private const PATTERN_INTEND = "/^(?<intend>.+)Delivery Details/m";

    private string $text;

    private int $intend;

    private ?string $pickupText;

    private ?string $deliveryText;

    public function parse(string $text): ?string
    {
        $this->text = $this->replaceBefore($text);

        return $this->setIntend()
            ->getPickupInstructions()
            ->getDeliveryInstructions()
            ->getResult();
    }

    private function setIntend(): DispatchInstructionsParser
    {
        preg_match(self::PATTERN_INTEND, $this->text, $match);

        $this->intend = mb_strlen($match['intend'])-2;

        return $this;
    }

    private function getPickupInstructions(): DispatchInstructionsParser
    {
        $this->pickupText = $this->normalizeInstructions(
            (string)preg_replace("/^(.{0," . $this->intend . "}).*$/m", "$1", $this->text)
        );

        return $this;
    }

    private function getDeliveryInstructions(): DispatchInstructionsParser
    {
        $this->deliveryText = $this->normalizeInstructions(
            (string)preg_replace("/^.{0," . $this->intend . "}(.*)$/m", "$1", $this->text)
        );

        return $this;
    }

    private function normalizeInstructions(string $info): ?string
    {
        if (!preg_match("/.+Instructions:(?<instructions>.+)/s", $info, $match)) {
            return null;
        }

        $info = $match['instructions'];

        $info = preg_replace("/\n/", " ", $info);

        $info = trim(preg_replace("/ {2,}/", " ", $info), " \t\n\r\0\x0B,.!?");

        return !empty($info) ? $info . '.' : null;
    }

    private function getResult(): ?string
    {
        if (!empty($this->pickupText)) {
            $result[] = $this->pickupText;
        }
        if (!empty($this->deliveryText)) {
            $result[] = $this->deliveryText;
        }

        return !empty($result) ? implode(" ", $result) : null;
    }
}
