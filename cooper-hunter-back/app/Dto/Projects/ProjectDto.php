<?php

namespace App\Dto\Projects;

class ProjectDto
{
    private string $name;

    /**
     * @var array<ProjectSystemDto>
     */
    private array $projectSystems = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];

        foreach ($args['systems'] ?? [] as $system) {
            $self->projectSystems[] = ProjectSystemDto::byArgs($system);
        }

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ProjectSystemDto[]
     */
    public function getProjectSystems(): array
    {
        return $this->projectSystems;
    }
}
