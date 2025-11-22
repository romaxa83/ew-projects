<?php

namespace Tests\Builders\History;


use App\Models\History\History;
use Illuminate\Database\Eloquent\Model;
use Tests\Builders\BaseBuilder;

class HistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return History::class;
    }

    public function model(Model $model): self
    {
        $this->data['model_type'] = get_class($model);
        $this->data['model_id'] = $model->id;
        return $this;
    }
}

