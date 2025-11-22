<?php

namespace Tests\Builders\Projects;

use App\Contracts\Members\Member;
use App\Models\Projects\Project;
use Tests\Builders\BaseBuilder;

class ProjectBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Project::class;
    }

    public function setMember(Member $member): self
    {
        $this->data = [
            'member_type' => get_class($member)::MORPH_NAME,
            'member_id' => $member->id,
        ];

        return $this;
    }
}
