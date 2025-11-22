<?php

namespace App\Dto\Catalog\Solutions;

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Support\Collection;

class SolutionDto
{

    private int $productId;

    private SolutionTypeEnum $type;

    private ?string $shortName = null;

    private ?int $seriesId = null;

    private ?SolutionZoneEnum $zone = null;

    /**@var null|SolutionClimateZoneEnum[] $climateZones */
    private ?array $climateZones = null;

    private ?SolutionIndoorEnum $indoorType = null;

    private ?int $btu = null;

    private ?int $maxBtuPercent = null;

    private ?int $voltage = null;

    /**@var SolutionLineSetDto[]|null $lineSets */
    private ?array $lineSets = null;

    private ?array $indoorIds = null;

    /**@var Collection|SolutionDefaultSchemaDto[]|null $defaultSchemas */
    private Collection|array|null $defaultSchemas = null;

    /**
     * @param array $args
     * @return static
     * @throws InvalidEnumKeyException
     */
    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->productId = (int)$args['product_id'];
        $dto->type = SolutionTypeEnum::fromKey($args['type']);

        if (!$dto->type->isLineSet()) {
            $dto->seriesId = (int)$args['series_id'];
            $dto->btu = (int)$args['btu'];

            if ($dto->type->isOutdoor()) {
                $dto->zone = SolutionZoneEnum::fromKey($args['zone']);
                $dto->maxBtuPercent = $args['max_btu_percent'];
                $dto->climateZones = array_map(
                    fn(string $climateZone) => SolutionClimateZoneEnum::fromKey($climateZone),
                    $args['climate_zones']
                );
                $dto->voltage = (int)$args['voltage'];
                $dto->indoorIds = $args['indoors'];

                if (!empty($args['default_schemas'])) {
                    $dto->defaultSchemas = collect();
                    foreach ($args['default_schemas'] as $schema) {
                        for ($i = 0; $i < $schema['count_zones']; $i++) {
                            $dto->defaultSchemas->push(
                                SolutionDefaultSchemaDto::byArgs(
                                    [
                                        'count_zones' => $schema['count_zones'],
                                        'zone' => $i + 1,
                                        'indoor_id' => $schema['indoors'][$i]
                                    ]
                                )
                            );
                        }
                    }
                }
            } else {
                $dto->indoorType = SolutionIndoorEnum::fromKey($args['indoor_type']);
                foreach ($args['line_sets'] as $lineSet) {
                    $dto->lineSets[] = SolutionLineSetDto::byArgs($lineSet);
                }
            }
        } else {
            $dto->shortName = $args['short_name'];
        }


        return $dto;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getType(): SolutionTypeEnum
    {
        return $this->type;
    }

    public function getSeriesId(): ?int
    {
        return $this->seriesId;
    }

    public function getZone(): ?SolutionZoneEnum
    {
        return $this->zone;
    }

    /**
     * @return SolutionClimateZoneEnum[]|null
     */
    public function getClimateZones(): ?array
    {
        return $this->climateZones;
    }

    public function getIndoorType(): ?SolutionIndoorEnum
    {
        return $this->indoorType;
    }

    public function getMaxBtuPercent(): ?int
    {
        return $this->maxBtuPercent;
    }

    public function getBtu(): ?int
    {
        return $this->btu;
    }

    public function getVoltage(): ?int
    {
        return $this->voltage;
    }

    /**
     * @return SolutionLineSetDto[]|null
     */
    public function getLineSets(): ?array
    {
        return $this->lineSets;
    }

    public function getIndoorIds(): ?array
    {
        return $this->indoorIds;
    }

    public function getDefaultSchemas(): ?Collection
    {
        return $this->defaultSchemas;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }
}


