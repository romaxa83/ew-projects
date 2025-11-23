<?php

namespace Tests\Builders\Divisions;

use App\Models\Division;
use Tests\Builders\BaseBuilder;

class DivisionBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Division::class;
    }

    public function misc(array $values): self
    {
        $this->data['miscs'] = $values;
        return $this;
    }

    public function id(int $values): self
    {
        $this->data['id'] = $values;
        return $this;
    }
}
