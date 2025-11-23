<?php

namespace Tests\Builders\Partners;

use App\Models\Division;
use App\Models\Partners\Partner;
use Tests\Builders\BaseBuilder;

class PartnerBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Partner::class;
    }

    public function division(Division $model): self
    {
        $this->data['division_id'] = $model->id;
        return $this;
    }
}
