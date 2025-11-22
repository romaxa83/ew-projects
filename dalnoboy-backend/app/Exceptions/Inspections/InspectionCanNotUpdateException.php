<?php


namespace App\Exceptions\Inspections;


use Core\Exceptions\TranslatedException;

class InspectionCanNotUpdateException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('inspections.can_not_update'));
    }
}
