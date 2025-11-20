<?php

namespace App\Services\Catalog;

use App\Abstractions\AbstractService;
use App\Models\Country;

class CountryService extends AbstractService
{
    public function create(array $data): Country
    {
        $model = new Country();
        $model->name = $data['name'];
        $model->active = $data['active'];

        $model->save();

        return $model;
    }
}

