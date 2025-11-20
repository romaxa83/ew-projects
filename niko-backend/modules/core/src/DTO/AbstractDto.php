<?php

namespace WezomCms\Core\DTO;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractDto
{
    protected $model;

    abstract public function toArray();

    public function setModel($model)
    {
        if($model instanceof Model){
            $this->model = $model;
        }

        return $this;
    }

    protected function coords($model)
    {
        $coords = [];
        if(isset($model->location) && $model->location){
            $coords['lat'] = $model->location->getLat();
            $coords['lon'] = $model->location->getLng();
        }
        return $coords;
    }

    protected function imagesAsArray(Collection $images)
    {
        $data = [];
        foreach ($images as $key => $image){
            $data[$key]['original'] = $image->getImage('original');
            $data[$key]['medium'] = $image->getImage('medium');
            $data[$key]['small'] = $image->getImage('small');
        }

        return $data;
    }
}
