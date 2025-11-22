<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Types\Content\OurCase\OurCaseType;
use App\Services\OurCases\OurCaseService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;

abstract class BaseOurCaseMutation extends BaseMutation
{
    public function __construct(protected OurCaseService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'our_case' => [
                'type' => $this->getInputType(),
            ],
        ];
    }

    abstract protected function getInputType(): Type;

    public function type(): Type
    {
        return OurCaseType::type();
    }
}
