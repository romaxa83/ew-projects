<?php

namespace App\Services\Tags;

use App\Dto\Tags\TagDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Tags\Tag;

class TagService
{
    public function __construct()
    {}

    public function create(TagDto $dto): Tag
    {
        $model = $this->fill(new Tag(), $dto);

        $model->save();

        return $model;
    }

    public function createFromSync(TagDto $dto): Tag
    {
        $model = $this->fill(new Tag(), $dto);
        $model->origin_id = $dto->originId;

        $model->save();

        return $model;
    }

    public function update(Tag $model, TagDto $dto): Tag
    {
        $model = $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(Tag $model, TagDto $dto): Tag
    {
        $model->name = $dto->name;
        $model->color = $dto->color;
        $model->type = $dto->type;

        return $model;
    }

    public function delete(Tag $model): bool
    {
        return $model->delete();
    }
}
