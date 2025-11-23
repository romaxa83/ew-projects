<?php

namespace Tests\Builders\Gmail;

use App\Models\Mailbox\Gmail\Account;
use App\Models\Mailbox\Gmail\Message;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class MessageBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Message::class;
    }

    public function account(Account $model): self
    {
        $this->data['account_id'] = $model->id;
        return $this;
    }

    public function tag(string $value): self
    {
        $this->data['tag'] = $value;
        return $this;
    }

    public function tags(string $value): self
    {
        $this->data['tags'] = $value;
        return $this;
    }

    public function misc(array $data): self
    {
        $this->data['miscs'] = $data;
        return $this;
    }

    public function updated_at(CarbonImmutable $value): self
    {
        $this->data['updated_at'] = $value;
        return $this;
    }
}
