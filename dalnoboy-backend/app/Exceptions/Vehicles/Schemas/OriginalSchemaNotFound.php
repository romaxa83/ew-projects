<?php


namespace App\Exceptions\Vehicles\Schemas;


use Core\Exceptions\TranslatedException;

class OriginalSchemaNotFound extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.schemas.original_not_found'));
    }
}
