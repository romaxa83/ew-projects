<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SolutionOutdoorNotFoundException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.catalog.solutions.outdoor_not_found'));
    }
}
