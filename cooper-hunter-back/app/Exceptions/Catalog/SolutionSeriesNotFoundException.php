<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SolutionSeriesNotFoundException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.catalog.solutions.series_not_found'));
    }
}
