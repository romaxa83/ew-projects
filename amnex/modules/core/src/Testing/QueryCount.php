<?php

namespace Wezom\Core\Testing;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\Constraint;

class QueryCount extends Constraint
{
    public function __construct(private readonly int $expected)
    {
        //
    }

    public function toString(): string
    {
        return sprintf(
            'Expected queries %d',
            $this->expected
        );
    }

    protected function matches(mixed $other): bool
    {
        return $this->expected === count($this->getExecutedQueries());
    }

    protected function getExecutedQueries(): array
    {
        return DB::getRawQueryLog();
    }

    protected function failureDescription(mixed $other): string
    {
        $queries = $this->getExecutedQueries();

        return sprintf('Expected %d queries to be called, but there are %d!', $this->expected, count($queries))
            . PHP_EOL
            . $this->showQueryLog();
    }

    public function showQueryLog(): string
    {
        $queries = $this->getExecutedQueries();

        return collect($queries)
            ->map(static fn (array $q, int $i) => sprintf(
                'Query %d (took: %dms):%s%s',
                $i + 1,
                $q['time'],
                PHP_EOL,
                $q['raw_query']
            ))
            ->prepend('List of all executed queries:')
            ->join(PHP_EOL);
    }
}
