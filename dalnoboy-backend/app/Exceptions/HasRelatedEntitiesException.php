<?php

namespace App\Exceptions;

use Core\Exceptions\TranslatedException;

class HasRelatedEntitiesException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.has_related_entities'));
    }
}
