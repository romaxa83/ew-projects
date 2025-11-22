<?php

namespace App\Dto\Catalog;

class SearchDto
{
    private ?string $query;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->query = data_get($args, 'query');

        return $self;
    }

    public function getFilter(): array
    {
        if (!$this->query) {
            return [];
        }

        return ['query' => $this->query];
    }
}
