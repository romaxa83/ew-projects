<?php

namespace Tests\Builder;

use App\Models\Image;

class ImageBuilder
{
    private $data = [];

    public function setEntity($model): self
    {
        $tmp = [
            'entity_type'  => $model::class,
            'entity_id'  => $model->id,
        ];
        $this->setData($tmp);

        return $this;
    }

    public function setData(array $value): self
    {
        $this->data = array_merge($this->data, $value);
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        $this->clear();

        return $model;
    }

    private function save()
    {
        return Image::factory()->create($this->data);
    }

    private function clear(): void
    {
        $this->data = [];
    }
}

