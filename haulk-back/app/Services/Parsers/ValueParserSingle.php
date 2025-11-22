<?php

namespace App\Services\Parsers;

use Exception;

class ValueParserSingle extends ValueParserAbstract
{
    public function parse(string $text)
    {
        $result = $this->replacementIntend($this->replaceBefore($text));

        $result = trim($this->find($result));

        $text = trim($this->replaceAfter($result));

        return !empty($text) ? $text : null;
    }

    /**
     * @param string $text
     * @return mixed|string
     * @throws Exception
     */
    protected function find(string $text)
    {
        $pattern = $this->parsingAttribute->pattern;
        if (preg_match($pattern, $text, $matches) && isset($matches[$this->parsingAttribute->name])) {
            return $matches[$this->parsingAttribute->name];
        }

        return '';
    }
}
