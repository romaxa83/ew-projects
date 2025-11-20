<?php

namespace App\Abstractions;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractService
{
    public function __construct()
    {}

    public function toggleActive(Model $model): Model
    {
        if(!method_exists($model, 'toggleActive')){
            throw new \Exception("Include Active Trait");
        }
        $model->toggleActive();

        return $model;
    }
}
