<?php

namespace App\Services\Projects;

use App\Contracts\Members\Member;
use App\Dto\Projects\ProjectDto;
use App\Models\Projects\Project;

class ProjectService
{
    public function __construct(
        protected SystemService $systemService
    ) {
    }

    public function create(ProjectDto $dto, Member $member): Project
    {
        $project = new Project();

        $this->fill($project, $dto);
        $this->setMember($project, $member);

        $project->touch();

        $this->createNewSystems($project, $dto);

        return $project;
    }

    protected function fill(Project $project, ProjectDto $dto): void
    {
        $project->name = $dto->getName();
    }

    protected function setMember(Project $project, Member $member): void
    {
        $project->member_type = $member->getMorphType();
        $project->member_id = $member->getId();
    }

    protected function createNewSystems(Project $project, ProjectDto $dto): void
    {
        foreach ($dto->getProjectSystems() as $systemDto) {
            $this->systemService->create($project, $systemDto);
        }
    }

    public function update(Project $project, ProjectDto $dto): Project
    {
        $this->fill($project, $dto);
        $project->touch();

        foreach ($dto->getProjectSystems() as $systemDto) {
            if ($systemDto->getId()) {
                $this->systemService->updateUsingDto($systemDto);
            } else {
                $this->systemService->create($project, $systemDto);
            }
        }

        return $project;
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }
}
