<?php

namespace App\Services\User;

use App\Models\User\Car;
use App\Models\User\Confidant;
use App\Services\Telegram\TelegramDev;
use App\ValueObjects\Phone;
use DB;

class CarConfidantsService
{

    public function __construct()
    {}

    public function createFromAA(Car $car, array $data){
        DB::beginTransaction();
        try {
            foreach ($data ?? [] as $item){
                $this->create($car, $item);

                TelegramDev::info("🔄 ADD Confidants: [{$item['name']}]",);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function create(Car $car, array $data): Confidant
    {
        $model = new Confidant();
        $model->car_id = $car->id;
        $model->uuid = $data['id'];
        $model->name = $data['name'];
        $model->phone = new Phone($data['number']);

        $model->save();

        return $model;
    }

    public function edit(Confidant $model, array $data): Confidant
    {
        $model->name = $data['name'];
        $model->phone = new Phone($data['number']);

        $model->save();

        return $model;
    }

}


