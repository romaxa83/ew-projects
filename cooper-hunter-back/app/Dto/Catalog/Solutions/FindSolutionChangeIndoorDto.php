<?php

namespace App\Dto\Catalog\Solutions;

class FindSolutionChangeIndoorDto
{

    private int $outdoorId;

    private int $countZones;

    private array $indoors;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->countZones = (int)data_get($args, 'count_zones', 1);
        $dto->outdoorId = $args['outdoor_id'];

        foreach ($args['indoors'] as $indoor) {
            $dto->indoors[] = SolutionIndoorSettingDto::byArgs($indoor);
        }

        return $dto;
    }

    public function getCountZones(): int
    {
        return $this->countZones;
    }

    public function getOutdoorId(): int
    {
        return $this->outdoorId;
    }

    /**
     * @return SolutionIndoorSettingDto[]
     */
    public function getIndoorsSetting(): array
    {
        return $this->indoors;
    }
}


