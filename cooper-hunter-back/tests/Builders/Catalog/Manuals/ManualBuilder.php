<?php

namespace Tests\Builders\Catalog\Manuals;

use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use Tests\Builders\BaseBuilder;

class ManualBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return Manual::class;
    }

    public function setGroup(ManualGroup $model): self
    {
        $this->data['manual_group_id'] = $model->id;

        return $this;
    }
}


