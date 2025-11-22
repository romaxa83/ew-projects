<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class CantChangeDeleteSolutionSettingException extends TranslatedException
{

    public function __construct(string $productName)
    {
        parent::__construct(
            trans('validation.custom.catalog.solutions.cant_change_type_and_delete', ['product' => $productName])
        );
    }
}
