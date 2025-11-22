<?php

namespace Core\Chat\Entities\Messages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonException;

class MessageMetaEntity implements Arrayable, Jsonable
{
    public function __construct(
        public string $url,
        public string $name
    ) {
    }

    public static function make(array $arr): self
    {
        return new self($arr['url'], $arr['name']);
    }

    /** @throws JsonException */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
