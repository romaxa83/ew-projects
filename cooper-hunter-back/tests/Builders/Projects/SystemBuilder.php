<?php

namespace Tests\Builders\Projects;

use App\Models\Projects\Project;
use App\Models\Projects\System;
use Tests\Builders\BaseBuilder;

class SystemBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return System::class;
    }

    public function setProject(Project $model): self
    {
        $this->data['project_id'] = $model->id;

        return $this;
    }
}
