<?php


namespace App\Dto\Catalog\Solutions;


class SolutionDefaultSchemaDto
{
    private int $countZones;
    private int $zone;
    private int $indoorId;


    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->countZones = $args['count_zones'];
        $dto->zone = $args['zone'];
        $dto->indoorId = $args['indoor_id'];

        return $dto;
    }

    public function getCountZones(): int
    {
        return $this->countZones;
    }

    public function getZone(): int
    {
        return $this->zone;
    }

    public function getIndoorId(): int
    {
        return $this->indoorId;
    }
}
