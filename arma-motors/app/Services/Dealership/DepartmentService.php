<?php

namespace App\Services\Dealership;

use App\DTO\Dealership\DepartmentDTO;
use App\DTO\Dealership\DepartmentTranslationDTO;
use App\DTO\Dealership\ScheduleDTO;
use App\Models\Dealership\Department;
use App\Models\Dealership\DepartmentTranslation;
use App\Models\Dealership\Schedule;

class DepartmentService
{
    public function __construct()
    {}

    public function create(DepartmentDTO $dto, string $dealershipId): Department
    {
        \DB::beginTransaction();
        try {
            $model = new Department();
            $model->dealership_id = $dealershipId;
            $model->active = $dto->getActive();
            $model->sort = $dto->getSort();
            $model->phone = $dto->getPhone();
            $model->email = $dto->getEmail();
            $model->telegram = $dto->getTelegram();
            $model->viber = $dto->getViber();
            $model->type = $dto->getType();
            $model->location = $dto->getLocation();

            $model ->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation DepartmentTranslationDTO */
                $t = new DepartmentTranslation();
                $t->department_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->address = $translation->getAddress();
                $t->save();
            }

            foreach ($dto->getSchedule() as $schedule){
                /** @var $schedule ScheduleDTO */
                $s = new Schedule();
                $s->department_id = $model->id;
                $s->day = $schedule->getDay();
                $s->from = $schedule->getFrom();
                $s->to = $schedule->getTo();
                $s->save();
            }

            \DB::commit();
            return $model;
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(DepartmentDTO $dto, Department $model): Department
    {
        \DB::beginTransaction();
        try {
            $model->active = $dto->getActive();
            $model->sort = $dto->getSort();
            $model->phone = $dto->getPhone();
            $model->email = $dto->getEmail();
            $model->telegram = $dto->getTelegram();
            $model->viber = $dto->getViber();
            $model->type = $dto->getType();
            $model->location = $dto->getLocation();

            $model ->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation DepartmentTranslationDTO */
                $t = $model->translations()->where('lang', $translation->getLang())->first();
                $t->name = $translation->getName();
                $t->address = $translation->getAddress();
                $t->save();
            }

            foreach ($dto->getSchedule() as $schedule){
                /** @var $schedule ScheduleDTO */
                $s = $model->schedule()->where('day', $schedule->getDay())->first();
                if(null != $s){
                    $s->from = $schedule->getFrom();
                    $s->to = $schedule->getTo();
                } else {
                    $s = new Schedule();
                    $s->department_id = $model->id;
                    $s->day = $schedule->getDay();
                    $s->from = $schedule->getFrom();
                    $s->to = $schedule->getTo();
                }
                $s->save();
            }

            \DB::commit();
            return $model;
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

