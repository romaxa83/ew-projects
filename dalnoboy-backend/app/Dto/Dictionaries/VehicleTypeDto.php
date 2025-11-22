<?php

namespace App\Dto\Dictionaries;

use App\Dto\TranslateDto;

class VehicleTypeDto
{
    use TranslateDto;

    private static string $translationDto = DictionaryTranslateDto::class;

    private array $vehicleClasses;
    private bool $active;


    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->vehicleClasses = $args['vehicle_classes'];
        $dto->active = $args['active'];
        $dto->setTranslations($args);

        return $dto;
    }

    public function getVehicleClasses(): array
    {
        return $this->vehicleClasses;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
