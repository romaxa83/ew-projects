<?php

namespace Tests\Builders\History;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\History\Models\History;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class HistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return History::class;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function model(BaseModel $model): self
    {
        $this->data['model_id'] = $model->id;
        $this->data['model_type'] = $model::MORPH_NAME;
        return $this;
    }

    public function performed_at(CarbonImmutable $value): self
    {
        $this->data['performed_at'] = $value;
        return $this;
    }

    public function msg(string $value): self
    {
        $this->data['msg'] = $value;
        return $this;
    }

    public function msg_attr(array $value): self
    {
        $this->data['msg_attr'] = $value;
        return $this;
    }

    public function details(array $value): self
    {
        $this->data['details'] = $value;
        return $this;
    }
}
