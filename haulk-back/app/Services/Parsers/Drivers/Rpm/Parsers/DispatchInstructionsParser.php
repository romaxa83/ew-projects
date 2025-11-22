<?php

namespace App\Services\Parsers\Drivers\Rpm\Parsers;

use App\Services\Parsers\ValueParserAbstract;

class DispatchInstructionsParser extends ValueParserAbstract
{
    private string $text;
    private array $texts = [];

    public function parse(string $text): ?string
    {
        $this->text = $this->replaceBefore($text, false);

        return $this
            ->setInstructions()
            ->setPDInstructions("/\n\s*Delivery {2,}.+/s", "")
            ->setPDInstructions("/.+\n( {3,}Delivery {2,}.+)/s", "$1", false)
            ->getResult();
    }

    private function setInstructions(): self
    {
        if (!preg_match("/\n\nNotes {2,}(?<notes>.+)/s", $this->text, $match)) {
            return $this;
        }
        $text = $this->clearText(
            preg_replace("/\n\nNotes/s", "", $match['notes'])
        );
        $this->text = preg_replace("/\n\nNotes.+/s", "", $this->text);
        if (!$text) {
            return $this;
        }
        $this->texts[] = trim($text);
        return $this;
    }

    private function setPDInstructions(string $pattern, string $replace, bool $pickup = true): self
    {
        $text = preg_replace($pattern, $replace, $this->text);
        $text = $this->replacementIntend($text);

        if (preg_match("/(^\n|^[^a-z]\n)/is", $text)) {
            $text = preg_replace("/^[^\n]*\n/s", "", $text);
        }
        $text = $this->clearText($text);
        if (empty($text)) {
            return $this;
        }
        $this->texts[] = ($pickup ? 'Pickup' : 'Delivery') . ': ' . $text;

        return $this;
    }

    private function clearText(string $text): ?string
    {
        $text = trim(
            preg_replace(
                "/Notes: /",
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
