<?php


namespace App\Exceptions\Branches;


use Core\Exceptions\TranslatedException;

class BranchHasEmployeesException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(
            trans('validation.custom.branches.branch_has_employees_yet')
        );
    }
}
