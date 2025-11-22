<?php

namespace Tests\Builders\TypeOfWorks;

use App\Models\TypeOfWorks\TypeOfWork;
use Tests\Builders\BaseBuilder;

class TypeOfWorkBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return TypeOfWork::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }
}
