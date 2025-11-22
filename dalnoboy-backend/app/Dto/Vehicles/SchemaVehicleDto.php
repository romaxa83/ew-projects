<?php


namespace App\Dto\Vehicles;


use Illuminate\Support\Collection;

class SchemaVehicleDto
{
    private string $name;
    private int $originalSchemaId;
    private Collection $wheels;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->name = $args['name'];
        $dto->originalSchemaId = $args['original_schema_id'];
        $dto->wheels = collect($args['wheels'])
            ->map(fn(string $item) => (int)$item);;

        return $dto;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOriginalSchemaId(): int
    {
        return $this->originalSchemaId;
    }

    public function getWheels(): Collection
    {
        return $this->wheels;
    }

}
