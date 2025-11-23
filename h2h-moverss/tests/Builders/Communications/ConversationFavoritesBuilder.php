<?php

namespace Tests\Builders\Communications;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationFavorites;
use App\User;
use Tests\Builders\BaseBuilder;

class ConversationFavoritesBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return ConversationFavorites::class;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
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

    public function communication_rec(?CommunicationRecord $model): self
    {
        $this->data['communication_rec_id'] = $model->id ?? null;
        return $this;
    }

    public function contact_value(string $value): self
    {
        $this->data['contact_value'] = $value;
        return $this;
    }

    public function starred(bool $value): self
    {
        $this->data['starred'] = $value;
        return $this;
    }
}

