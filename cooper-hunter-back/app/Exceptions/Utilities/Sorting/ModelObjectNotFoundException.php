<?php


namespace App\Exceptions\Utilities\Sorting;


use Core\Exceptions\TranslatedException;

class ModelObjectNotFoundException extends TranslatedException
{

    public function __construct(int $id)
    {
        parent::__construct(
            trans('validation.custom.utilities.sorting.model_object_not_found', ['id' => $id])
        );
    }
}
