<?php

namespace App\Dto\Catalog\Solutions;

class SolutionIndoorSettingDto
{

    private int $btu;

    private int $seriesId;

    private string $type;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->btu = $args['btu'];
        $dto->seriesId = $args['series_id'];
        $dto->type = $args['type'];

        return $dto;
    }

    public function getBtu(): int
    {
        return $this->btu;
    }

    public function getSeriesId(): int
    {
        return $this->seriesId;
    }

    public function getType(): string
    {
        return $this->type;
    }
}


