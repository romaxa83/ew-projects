<?php


namespace App\Exceptions\Utilities;


use Core\Exceptions\TranslatedException;

class SortDirectionException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.utilities.sort_direction'));
    }
}
