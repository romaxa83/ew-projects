<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories;

use App\GraphQL\Types\Content\OurCase\OurCaseCategoryType;
use App\Services\OurCases\OurCaseCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;

abstract class BaseOurCaseCategoryMutation extends BaseMutation
{
    public function __construct(protected OurCaseCategoryService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'our_case_category' => [
                'type' => $this->getInputType(),
            ],
        ];
    }

    abstract protected function getInputType(): Type;

    public function type(): Type
    {
        return OurCaseCategoryType::type();
    }
}
