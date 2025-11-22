<?php


namespace App\Exceptions\Utilities;


use Core\Exceptions\TranslatedException;

class NothingToExportException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(
            trans('validation.custom.utilities.nothing_to_export')
        );
    }
}
