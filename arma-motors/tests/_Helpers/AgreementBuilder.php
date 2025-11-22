<?php

namespace Tests\_Helpers;

use App\Models\Agreement\Agreement;
use App\Models\Agreement\Job;
use App\Models\Agreement\Part;

class AgreementBuilder
{
    private $data = [];

    public function setStatus($value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function setAcceptedAt($value): self
    {
        $this->data['accepted_at'] = $value;
        return $this;
    }

    public function setUuid($value): self
    {
        $this->data['uuid'] = $value;
        return $this;
    }

    public function setUserUuid($value): self
    {
        $this->data['user_uuid'] = $value;
        return $this;
    }

    public function setCarUuid($value): self
    {
        $this->data['car_uuid'] = $value;
        return $this;
    }

    public function setBaseOrderUuid($value): self
    {
        $this->data['base_order_uuid'] = $value;
        return $this;
    }

    public function setDealershipAlias($value): self
    {
        $this->data['dealership_alias'] = $value;
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        $this->createJobs($model->id);
        $this->createJobs($model->id);

        $this->createParts($model->id);
        $this->createParts($model->id);

        return $model;
    }

    private function save()
    {
        return Agreement::factory()->new($this->data)->create();
    }

    private function createJobs($ID)
    {
        return Job::factory()->create(['agreement_id' => $ID]);
    }

    private function createParts($ID)
    {
        return Part::factory()->create(['agreement_id' => $ID]);
    }
}

