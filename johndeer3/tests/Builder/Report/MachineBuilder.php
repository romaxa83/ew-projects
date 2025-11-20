<?php

namespace Tests\Builder\Report;

use App\Models\JD\ModelDescription;
use App\Models\Report\Report;

class MachineBuilder
{
    private $data = [];

    public function setStatus($value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function setModelDescription(ModelDescription $model): self
    {
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        return $model;
    }

    private function save()
    {
        return Report::factory()->create($this->data);
    }
}

