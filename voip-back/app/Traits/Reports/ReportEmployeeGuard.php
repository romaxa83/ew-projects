<?php

namespace App\Traits\Reports;

use App\Models\Employees\Employee;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Rebing\GraphQL\Error\AuthorizationError;

trait ReportEmployeeGuard
{
    public function itemsGuard($args)
    {
        if(
            $this->user() instanceof Employee
            && $this->user()->report->id != $args['report_id']
        ){
            throw new AuthorizationError(AuthorizationMessageEnum::NO_PERMISSION);
        }
    }

    public function modifyArgs(array $args): array
    {
        if($this->user() instanceof Employee){
            $args['employee_id'] = $this->user()->id;
        }

        return $args;
    }
}

