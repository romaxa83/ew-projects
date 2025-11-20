<?php

namespace Tests\Builder\Feature;

use App\Models\Report\Feature\Feature;
use App\Models\Report\Feature\FeatureTranslation;
use App\Models\Report\Feature\FeatureValue;
use App\Models\Report\Feature\FeatureValueTranslates;

class FeatureBuilder
{
    private $data = [];
    private $values = [];
    private $egIds = [];
    private $subEgIds = [];
    private $withTranslation = false;

    public function setType($value): self
    {
        $this->data['type'] = $value;
        return $this;
    }

    public function setPosition($value): self
    {
        $this->data['position'] = $value;
        return $this;
    }

    public function setTypeField($value): self
    {
        $this->data['type_field'] = $value;
        return $this;
    }

    public function setActive($value): self
    {
        $this->data['active'] = $value;
        return $this;
    }

    public function setTypeFeature($value): self
    {
        $this->data['type_feature'] = $value;
        return $this;
    }

    public function setValues(...$value): self
    {
        $this->values = $value;
        return $this;
    }

    public function setEgIds(...$value): self
    {
        $this->egIds = $value;
        return $this;
    }

    public function setSubEgIds(...$value): self
    {
        $this->subEgIds = $value;
        return $this;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if($this->withTranslation){
            FeatureTranslation::factory()->create([
                'feature_id' => $model->id
            ]);
        }

        if(!empty($this->values)){
            foreach ($this->values as $val){
                $value = FeatureValue::factory()->create([
                    'feature_id' => $model->id
                ]);
                FeatureValueTranslates::factory()->create([
                    'name' => $val,
                    'value_id' => $value->id
                ]);
            }
        }

        if(!empty($this->egIds)){
            $model->egs()->attach($this->egIds);
        }

        if(!empty($this->subEgIds)){
            $model->subEgs()->attach($this->subEgIds);
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        return Feature::factory()->create($this->data);
    }
    private function clear()
    {
        $this->data = [];
        $this->values = [];
        $this->egIds = [];
        $this->subEgIds = [];
        $this->withTranslation = false;
    }

}

