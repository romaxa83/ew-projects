<?php

namespace Tests\Builders\Commercial\Commissioning;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\Protocol;

use Tests\Builders\BaseBuilder;

class ProjectProtocolBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return ProjectProtocol::class;
    }

    public function setProtocol(Protocol $model): self
    {
        $this->data['protocol_id'] = $model->id;
        $this->data['sort'] = $model->sort;

        return $this;
    }

    public function setProject(CommercialProject $model): self
    {
        $this->data['project_id'] = $model->id;

        return $this;
    }

    public function setStatus($value): self
    {
        $this->data['status'] = $value;

        return $this;
    }
}

