<?php

namespace Tests\Builders\Trucks;

use App\Models\Truck\Notes;
use App\Models\Truck\Truck;
use App\User;
use Tests\Builders\BaseBuilder;

class NoteBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Notes::class;
    }

    public function truck(Truck $model): self
    {
        $this->data['truck_id'] = $model->id;
        return $this;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }
}

