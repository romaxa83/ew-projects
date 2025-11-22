<?php


namespace App\GraphQL\Mutations\FrontOffice\Alerts;


use App\GraphQL\Mutations\Common\Alerts\BaseAlertSetReadMutation;

class AlertSetReadMutation extends BaseAlertSetReadMutation
{
    protected function setQueryGuard(): void
    {
        $this->setMemberGuard();
    }
}
