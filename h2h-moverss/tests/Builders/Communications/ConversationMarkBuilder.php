<?php

namespace Tests\Builders\Communications;

use App\Models\Client;
use App\Models\Communications\ConversationMark;
use App\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class ConversationMarkBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return ConversationMark::class;
    }

    public function client(?Client $model): self
    {
        $this->data['client_id'] = $model?->id;
        return $this;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function contact_type(string $value): self
    {
        $this->data['contact_type'] = $value;
        return $this;
    }

    public function contact_value(string $value): self
    {
        $this->data['contact_value'] = $value;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
}

