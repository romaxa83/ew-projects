<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\QueryBuilder;

use ArrayIterator;
use IteratorAggregate;

class UnionValue implements IteratorAggregate
{
    public function __construct(protected array $fields)
    {
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->fields);
    }
}
