<?php

namespace App\Services\Saas\GPS\Histories;

use App\Models\GPS\Route;

class RouteService
{
    public function create(array $data): Route
    {
        $model = null;
        if(data_get($data, 'truck_id')){
            $model = Route::query()
                ->where('truck_id', data_get($data, 'truck_id'))
                ->where('date', data_get($data, 'date'))
                ->first();
        }
        if(data_get($data, 'trailer_id')){
            $model = Route::query()
                ->where('trailer_id', data_get($data, 'trailer_id'))
                ->where('date', data_get($data, 'date'))
                ->first();
        }

        if($model){
            $model->data = data_get($data, 'data');
        } else {
            $model = new Route();
            $model->truck_id = data_get($data, 'truck_id');
            $model->trailer_id = data_get($data, 'trailer_id');
            $model->date = data_get($data, 'date');
            $model->data = data_get($data, 'data');
        }

        $model->save();

        return $model;
    }
}
