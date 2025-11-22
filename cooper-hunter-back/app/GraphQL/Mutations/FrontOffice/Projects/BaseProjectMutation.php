<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\GraphQL\Types\Projects\ProjectType;
use App\Services\Projects\ProjectService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;

abstract class BaseProjectMutation extends BaseMutation
{
    public function __construct(protected ProjectService $service)
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return ProjectType::type();
    }
}
