<?php

namespace Core\Testing\GraphQL\QueryBuilder;

class GraphQLSelect
{
    use GraphQLBuilderTrait;

    public function __construct(public array|string $select)
    {
    }

    public function getSelect(): string
    {
        return $this->convertToGQLSelect();
    }

    protected function convertToGQLSelect(): string
    {
        if (is_string($this->select)) {
            return $this->wrapAsObject($this->select);
        }

        if (count($this->select) === 0) {
            return '';
        }

        return $this->wrapAsObject(
            $this->implodeRecursive(' ', $this->select)
        );
    }

    protected function wrapAsObject(string $select): string
    {
        if (empty($select)) {
            return '';
        }

        if (!str_starts_with($select, '{')) {
            $select = '{' . $select;
        }

        if (!str_ends_with($select, '}')) {
            $select .= '}';
        }

        return $select;
    }
}
