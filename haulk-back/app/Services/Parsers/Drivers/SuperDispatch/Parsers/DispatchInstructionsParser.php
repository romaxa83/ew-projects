<?php

namespace App\Services\Parsers\Drivers\SuperDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;

class DispatchInstructionsParser extends ValueParserAbstract
{
    private string $text;
    private array $texts = [];

    public function parse(string $text): ?string
    {
        $this->text = $this->replaceBefore($text, false);

        return $this->setPDInstruction()
            ->setInstructions()
            ->getResult();
    }

    private function setPDInstruction(): self
    {
        $text = preg_replace("/\s*DISPATCH INSTRUCTIONS.*/s", "", $this->text);
        preg_match("/^(?<intend>(Notes:)?[^\n]+ {3,})Notes:/m", $text, $match);
        if (empty($match['intend'])) {
            return $this;
        }
        $intend = mb_strlen($match['intend']);

        $pickup = $this->clearText(
            preg_replace(
                "/^(.{0," . $intend . "}).*$/m",
                "$1",
                $text
            )
        );

        $delivery = $this->clearText(
            preg_replace(
                "/^.{0," . $intend . "}/m",
                "",
                $text
            )
        );
        if ($pickup) {
            $this->texts[] = trim('Pickup: ' . $pickup);
        }
        if ($delivery) {
            $this->texts[] = trim('Delivery: ' . $delivery);
        }
        return $this;
    }

    private function setInstructions(): self
    {
        $text = $this->clearText(
            preg_replace("/.*DISPATCH INSTRUCTIONS\s*/s", "", $this->text),
            false
        );
        if (!$text) {
            return $this;
        }
        $this->texts[] = trim($text);
        return $this;
    }

    private function clearText(string $text, bool $notes = true): ?string
    {
        if ($notes && !preg_match("/Notes:/s", $text)) {
            return null;
        }
        $text = trim(
            preg_replace(
                "/.*Notes: /",
                "",
                preg_replace(
                    "/ {2,}/",
                    " ",
                    preg_replace(
                        "/\n/",
                        " ",
                        $text
                    )
                )
            ),
            " \t\n\r\0\x0B*"
        );
        return !empty($text) ? $text : null;
    }

    private function getResult(): ?string
    {
        if (empty($this->texts)) {
            return null;
        }

        $this->texts = array_unique($this->texts);

        return implode("\n", $this->texts);
    }
}
