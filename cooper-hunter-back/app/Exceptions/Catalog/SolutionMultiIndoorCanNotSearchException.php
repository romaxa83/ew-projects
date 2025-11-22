<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SolutionMultiIndoorCanNotSearchException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.catalog.solutions.multi_indoors_cant_search'));
    }
}
