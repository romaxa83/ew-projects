<?php

namespace App\Services\User;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

class LoyaltyItemService extends BaseService
{
    public function __construct()
    {}

    public function toggleActiveFromBase(Model $model, bool $active): Model
    {
        try {
            $model->active = $active;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
