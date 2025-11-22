<?php


namespace App\Exceptions\Inspections;


use Core\Exceptions\TranslatedException;

class InspectionValidationException extends TranslatedException
{
    public function __construct(string $translateKey)
    {
        parent::__construct(trans($translateKey));
    }
}
