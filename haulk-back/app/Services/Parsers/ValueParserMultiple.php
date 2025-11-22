<?php

namespace App\Services\Parsers;

use Exception;
use Illuminate\Support\Collection;

class ValueParserMultiple extends ValueParserAbstract
{
    /**
     * @param string $text
     * @return Collection
     * @throws Exception
     */
    public function parse(string $text): Collection
    {
        $result = $this->replaceBefore($text);

        $results = [];
        foreach ($this->parsingAttribute->pattern as $pattern) {
            preg_match_all($pattern, $result, $matches);
            $results = $matches + $results;
        }
        return collect($this->normalize($results));
    }

    protected function normalize($matches): array
    {
        $filtered = $this->filterNumericKeys($matches);
        $filtered = $this->format($filtered);

        return $this->filterEmptyValues($filtered);
    }

    protected function filterEmptyValues(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->filterEmptyValues($value);
            } elseif (!empty($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function filterNumericKeys(array $array): array
    {
        return array_filter(
            $array,
            function ($key) {
                return !is_int($key);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function format(array $array): array
    {
        $result = [];

        foreach ($array as $attributeName => $values) {
            foreach ($values as $index => $value) {
                $result[$index][$attributeName] = $this->replaceAfter(trim($value));
            }
        }

        return $result;
    }
}
