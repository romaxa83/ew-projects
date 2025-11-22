<?php

namespace App\Dto\Dictionaries;

use App\Dto\TranslateDto;

class VehicleClassDto
{
    use TranslateDto;

    private static string $translationDto = DictionaryTranslateDto::class;

    private string $vehicleForm;
    private bool $active;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->vehicleForm = $args['vehicle_form'];
        $dto->active = $args['active'];
        $dto->setTranslations($args);

        return $dto;
    }

    public function getVehicleForm(): string
    {
        return $this->vehicleForm;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
