<?php


namespace App\GraphQL\Mutations\BackOffice\Inspections;


use App\GraphQL\Mutations\Common\Inspections\BaseInspectionUpdateMutation;
use App\GraphQL\Types\NonNullType;
use App\Models\Inspections\Inspection;

class InspectionUpdateMutation extends BaseInspectionUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'id' => [
                    'type' => NonNullType::id(),
                    'rules' => [
                        'required',
                        'int',
                        Inspection::ruleExists()
                    ]
                ],
            ]
        );
    }
}
