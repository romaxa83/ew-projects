<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SolutionBtuNotFoundException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.catalog.solutions.btu_not_found'));
    }
}
