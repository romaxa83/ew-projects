<?php

namespace App\Services\Parsers;

use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use Exception;

abstract class ValueParserAbstract
{
    /**
     * @var ParserPart
     */
    protected ParserPart $parsingAttribute;

    public function __construct(ParserPart $parserGroup)
    {
        $this->parsingAttribute = $parserGroup;
    }

    /**
     * @param string $text
     * @return string|string[]|null
     * @throws Exception
     */
    abstract public function parse(string $text);

    protected function replaceBefore(string $value, bool $trim = true): string
    {
        if (!$this->parsingAttribute->isNeedReplacementBefore()) {
            return $value;
        }
        $result = self::replaceByReplacement($value, $this->parsingAttribute->replacementBefore);
        return $trim === true ? trim($result) : $result;
    }

    public static function replaceByReplacement(string $value, array $replacement)
    {
        ksort($replacement);
        foreach ($replacement as $pattern => $replaceable) {
            $limit = -1;
            if (is_array($replaceable)) {
                $limit = !empty($replaceable[2]) ? $replaceable[2] : $limit;
                [$pattern, $replaceable] = $replaceable;
            }
            $value = preg_replace($pattern, $replaceable, $value, $limit);
        }

        return $value;
    }

    protected function replaceAfter(string $value): string
    {
        if (!$this->parsingAttribute->isNeedReplacementAfter()) {
            return $value;
        }

        return self::replaceByReplacement($value, $this->parsingAttribute->replacementAfter);
    }

    protected function replacementIntend(string $text): string
    {
        if (!$this->parsingAttribute->isNeedReplacementIntend()) {
            return $text;
        }

        foreach ($this->parsingAttribute->replacementIntend as $item) {
            /**@var ParserPartIntend $item*/
            $text = $item->clearIntend($text);
        }

        return $text;
    }

    protected function parseLocation(string $pattern, string $text, array $fields = ['city', 'state', 'zip']): array
    {
        $lines = explode("\n", $text);
        $lines = array_map('ltrim', $lines);
        $text = implode("\n", $lines);

        preg_match($pattern, $text, $match);

        $result = [];

        foreach ($fields as $field) {
            $result[$field] = !empty($match[$field]) ? preg_replace("/\n/", " ", trim($match[$field], " \t\n\r\0\x0B.,!?")) : null;
        }
        if (!empty($result['city'])) {
            $result['city'] = ucwords($result['city']);
        }
        return $result;
    }
}
