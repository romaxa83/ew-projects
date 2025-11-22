<?php

namespace App\Services\Parsers\Objects;

class ParserPartIntend
{
    public const TYPE_LEFT = 'left';
    public const TYPE_RIGHT = 'right';

    public string $pattern;
    public string $type;
    public ?array $clearAfter;

    public static function init(array $setting): self
    {
        $parser = new self();
        $parser->pattern = $setting['pattern'];
        $parser->type = $setting['type'];
        $parser->clearAfter = data_get($setting, 'clear_after');
        return $parser;
    }

    public static function left(string $pattern): self
    {
        return self::init(
            [
                'type' => self::TYPE_LEFT,
                'pattern' => $pattern
            ]
        );
    }

    public static function right(string $pattern): self
    {
        return self::init(
            [
                'type' => self::TYPE_RIGHT,
                'pattern' => $pattern
            ]
        );
    }

    public function setClearAfter(array $clearAfter): self
    {
        $this->clearAfter = $clearAfter;
        return $this;
    }

    public function clearIntend(string $text): string
    {
        if (!preg_match($this->pattern, $text, $match)) {
            return $text;
        }

        $intend = strlen($match['intend']);
        $text = self::refactorText($intend, $text);
        $text = self::removeIntent($text, $intend, $this->type);

        return $this->clearAfter($text);
    }

    public static function removeIntent(string $text, int $intend, string $type): string
    {
        if ($type === self::TYPE_LEFT) {
            return (string)preg_replace("/^.{0," . $intend . "}/m", "", $text);
        }
        return (string)preg_replace("/^(.{0," . $intend . "}).*$/m", "$1", $text);
    }

    public static function refactorText(int $intend, string $text): string
    {
        $text = explode("\n", $text);
        $key = $intend - 1;
        foreach ($text as &$item) {
            if (strlen($item) < $intend) {
                continue;
            }
            $char = $item[$key];
            if ($char === ' ') {
                continue;
            }
            $spaces = '';
            for ($i = $key; ; $i--) {
                if ($i < 0) {
                    break;
                }
                if ($item[$i] !== ' ') {
                    $spaces .= ' ';
                    continue;
                }
                break;
            }
            $item = preg_replace("/^(.{" . $i . "})/", "$1" . $spaces, $item);
        }
        return implode("\n", $text);
    }

    private function clearAfter(string $text): string
    {
        if (is_null($this->clearAfter) || !is_array($this->clearAfter)) {
            return $text;
        }

        foreach ($this->clearAfter as $pattern) {
            $text = (string)preg_replace($pattern[0], $pattern[1], $text);
        }

        return $text;
    }
}
