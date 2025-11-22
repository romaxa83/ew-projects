<?php


namespace App\Dto\Vehicles;


use App\Enums\Vehicles\VehicleFormEnum;

class VehicleDto
{
    private string $stateNumber;
    private ?string $vin;
    private bool $isModerated;
    private VehicleFormEnum $form;
    private int $classId;
    private int $typeId;
    private int $makeId;
    private int $modelId;
    private int $clientId;
    private int $schemaId;
    private ?int $odo;
    private bool $active;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->stateNumber = $args['state_number'];
        $dto->vin = data_get($args, 'vin');
        $dto->isModerated = $args['is_moderated'];
        $dto->form = VehicleFormEnum::fromValue($args['form']);
        $dto->classId = (int)$args['class_id'];
        $dto->typeId = (int)$args['type_id'];
        $dto->makeId = (int)$args['make_id'];
        $dto->modelId = (int)$args['model_id'];
        $dto->clientId = (int)$args['client_id'];
        $dto->schemaId = (int)$args['schema_id'];
        $dto->odo = isset($args['odo']) ? (int)$args['odo'] : null;
        $dto->active = $args['active'];

        return $dto;
    }

    /**
     * @return string
     */
    public function getStateNumber(): string
    {
        return $this->stateNumber;
    }

    /**
     * @return string|null
     */
    public function getVin(): ?string
    {
        return $this->vin;
    }

    /**
     * @return bool
     */
    public function getIsModerated(): bool
    {
        return $this->isModerated;
    }

    /**
     * @return VehicleFormEnum
     */
    public function getForm(): VehicleFormEnum
    {
        return $this->form;
    }

    /**
     * @return int
     */
    public function getClassId(): int
    {
        return $this->classId;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @return int
     */
    public function getMakeId(): int
    {
        return $this->makeId;
    }

    /**
     * @return int
     */
    public function getModelId(): int
    {
        return $this->modelId;
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @return int
     */
    public function getSchemaId(): int
    {
        return $this->schemaId;
    }

    /**
     * @return int|null
     */
    public function getOdo(): ?int
    {
        return $this->odo;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
