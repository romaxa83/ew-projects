<?php

namespace Tests\Builders\Tags;

use App\Enums\Tags\TagType;
use App\Models\Tags\Tag;
use Tests\Builders\BaseBuilder;

class TagBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Tag::class;
    }

    public function type(TagType $value): self
    {
        $this->data['type'] = $value;
        return $this;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function origin_id(int $value): self
    {
        $this->data['origin_id'] = $value;
        return $this;
    }
}
