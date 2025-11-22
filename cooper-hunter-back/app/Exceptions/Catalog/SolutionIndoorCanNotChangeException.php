<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SolutionIndoorCanNotChangeException extends TranslatedException
{

    public function __construct(int $zone)
    {
        parent::__construct(trans('validation.custom.catalog.solutions.change_indoor_not_found', ['zone' => $zone]));
    }
}
