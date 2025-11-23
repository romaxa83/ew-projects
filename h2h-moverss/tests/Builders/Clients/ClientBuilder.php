<?php

namespace Tests\Builders\Clients;

use App\Models\Client;
use Tests\Builders\BaseBuilder;

class ClientBuilder extends BaseBuilder
{
    private array $tags = [];

    function modelClass(): string
    {
        return Client::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function lname(string $value): self
    {
        $this->data['lname'] = $value;
        return $this;
    }

    public function tags(Client\Tag ...$values): self
    {
        $this->tags = $values;
        return $this;
    }

    protected function afterSave($model): void
    {
        if(!empty($this->tags)) {
            $ids = [];
            foreach ($this->tags as $tag) {
                $ids[] = $tag->id;
            }

            $model->tags()->attach($ids);
        }
    }

    protected function afterClear(): void
    {
        $this->tags = [];
    }
}
