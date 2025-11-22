<?php

namespace App\Dto;

class ModelGuidsDto
{
    /** @var array<UpdateGuidDto> */
    private array $dto;

    public static function byArgs(array $args): static
    {
        $instance = new static();

        foreach ($args['data'] as $arg) {
            $instance->dto[] = UpdateGuidDto::byArgs($arg);
        }

        return $instance;
    }

    public function getIds(): array
    {
        $response = [];

        foreach ($this->getDto() as $guidDto) {
            $response[] = $guidDto->getId();
        }

        return $response;
    }

    /**
     * @return UpdateGuidDto[]
     */
    public function getDto(): array
    {
        return $this->dto;
    }
}
