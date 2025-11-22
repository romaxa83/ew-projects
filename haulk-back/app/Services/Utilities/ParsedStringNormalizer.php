<?php

namespace App\Services\Utilities;

class ParsedStringNormalizer
{
    public function normalize(string $string): string
    {
        return trim($this->pipeline($string));
    }

    public function pipeline(string $string): string
    {
        $result = $string;

        foreach ($this->rules() as $search => $replace) {
            $result = str_replace($search, $replace, $result);
        }

        return $result;
    }

    public function rules(): array
    {
        return [
            "\n" => ' ',
        ];
    }
}
