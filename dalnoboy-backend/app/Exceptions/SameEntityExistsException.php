<?php

namespace App\Exceptions;

use Core\Exceptions\TranslatedException;

class SameEntityExistsException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.same_entity_exists'));
    }
}
