<?php

namespace Tests\_Helpers;

use App\Models\AA\AAPost;
use App\Models\AA\AAPostSchedule;
use Carbon\CarbonImmutable;

class AAPostBuilder
{
    private $withSchedule = false;

    private $data = [];
    private $dataSchedule = [];

    public function setAlias(string $value): self
    {
        $this->data['alias'] = $value;
        return $this;
    }

    public function setSchedule($value): self
    {
        $this->dataSchedule = $value;
        return $this;
    }

    public function withSchedule(): self
    {
        $this->withSchedule = true;
        return $this;
    }

    public function create()
    {
        $model = $this->save();
        if(!empty($this->dataSchedule)){
            foreach ($this->dataSchedule as $item){
                $item['post_id'] = $model->uuid;
                AAPostSchedule::factory()->create($item);
            }
        }


        if($this->withSchedule) {
            $today = CarbonImmutable::today()->subDay();
            AAPostSchedule::factory()->create([
                'date' => $today,
                'start_work' => $today->addHours(8),
                'end_work' => $today->addHours(20),
                'post_id' => $model->uuid,
                'work_day' => true
            ]);

            $today = CarbonImmutable::today();
            AAPostSchedule::factory()->create([
                'date' => $today,
                'start_work' => $today->addHours(8),
                'end_work' => $today->addHours(20),
                'post_id' => $model->uuid,
                'work_day' => true
            ]);

            $today = CarbonImmutable::today()->addDay();
            AAPostSchedule::factory()->create([
                'date' => $today,
                'start_work' => $today->addHours(8),
                'end_work' => $today->addHours(20),
                'post_id' => $model->uuid,
                'work_day' => true
            ]);
        }

        return $model;
    }

    private function save()
    {
        return AAPost::factory()->new($this->data)->create();
    }
}

