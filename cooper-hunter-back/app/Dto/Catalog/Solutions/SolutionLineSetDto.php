<?php


namespace App\Dto\Catalog\Solutions;


use App\Enums\Solutions\SolutionZoneEnum;

class SolutionLineSetDto
{
    private int $lineSetId;

    /**@var SolutionZoneEnum[]|null $defaultForZone */
    private ?array $defaultForZones = null;

    public static function byArgs(array $lineSet): self
    {
        $dto = new self();

        $dto->lineSetId = (int)$lineSet['line_set_id'];

        if (empty($lineSet['default_for_zones'])) {
            return $dto;
        }

        $dto->defaultForZones = array_map(
            fn(string $item) => SolutionZoneEnum::fromValue($item),
            $lineSet['default_for_zones']
        );

        return $dto;
    }

    /**
     * @return int
     */
    public function getLineSetId(): int
    {
        return $this->lineSetId;
    }

    /**
     * @return SolutionZoneEnum[]|null
     */
    public function getDefaultForZones(): ?array
    {
        return $this->defaultForZones;
    }
}
