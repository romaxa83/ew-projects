<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SolutionIncorrectZoneCountException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.catalog.solutions.incorrect_count_zones'));
    }
}
