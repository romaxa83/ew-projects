<?php

namespace Tests\Builders\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use Tests\Builders\BaseBuilder;

class ProjectAdditionBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return CommercialProjectAddition::class;
    }

    public function setProject(CommercialProject $model): self
    {
        $this->data['commercial_project_id'] = $model->id;

        return $this;
    }
}
