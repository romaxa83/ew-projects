<?php

namespace Tests\Builders\Companies;

use App\Models\Companies\Company;
use Tests\Builders\BaseBuilder;

class CompanyBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Company::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function id(int $value): self
    {
        $this->data['id'] = $value;
        return $this;
    }
}
