<?php

namespace App\Dto\Projects;

class ProjectSystemDto
{
    private ?int $id;
    private string $name;
    private ?string $description;

    /**
     * @var array<ProjectSystemUnitDto>
     */
    private array $units = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->id = $args['id'] ?? null;
        $self->name = $args['name'];
        $self->description = $args['description'] ?? null;

        foreach ($args['units'] ?? [] as $unit) {
            $self->units[] = ProjectSystemUnitDto::byArgs($unit);
        }

        return $self;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return ProjectSystemUnitDto[]
     */
    public function getUnits(): array
    {
        return $this->units;
    }
}
