<?php


namespace App\Exceptions\Utilities;


use Core\Exceptions\TranslatedException;

class SortFieldException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.utilities.sort_field'));
    }
}
