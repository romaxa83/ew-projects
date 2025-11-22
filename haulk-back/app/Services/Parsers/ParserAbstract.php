<?php


namespace App\Services\Parsers;


abstract class ParserAbstract
{
    public function options(): array
    {
        return [
            'layout'
        ];
    }

    public function preCheck(string $text): void
    {
    }

    abstract public function type(): string;

    abstract public function parts(): array;
}
