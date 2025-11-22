<?php

namespace App\Documents\Filters;

abstract class DocumentFilter
{
    public const MUST = 'must';
    public const MUST_NOT = 'must_not';
    public const SHOULD = 'should';
    public const FILTER = 'filter';

    private array $query;

    public function __construct()
    {
    }

    public function getFilter(): array
    {
        return $this->query ?? [];
    }

    /**
     * @return static
     */
    protected function addBoolQuery(string $type, array $args): self
    {
        $this->query = self::makeBoolQuery($this->getQuery(), $type, $args);
        return $this;
    }

    public static function makeBoolQuery(array $query, string $type, array $args): array
    {
        if (empty($query['bool'])) {
            $query['bool'] = [];
        }
        if (empty($query['bool'][$type])) {
            $query['bool'][$type] = [];
        }
        $query['bool'][$type][] = $args;
        return $query;
    }

    private function getQuery(): array
    {
        if (isset($this->query)) {
            return $this->query;
        }
        $this->query = [];
        return $this->query;
    }
}
