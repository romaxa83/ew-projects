<?php


namespace App\Exceptions\Vehicles\Schemas;


use Core\Exceptions\TranslatedException;

class SimilarSchemaException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.schemas.similar_schema'));
    }
}
