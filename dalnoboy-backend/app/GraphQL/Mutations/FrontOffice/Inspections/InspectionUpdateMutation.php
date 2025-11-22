<?php


namespace App\GraphQL\Mutations\FrontOffice\Inspections;


use App\GraphQL\Mutations\Common\Inspections\BaseInspectionUpdateMutation;

class InspectionUpdateMutation extends BaseInspectionUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
