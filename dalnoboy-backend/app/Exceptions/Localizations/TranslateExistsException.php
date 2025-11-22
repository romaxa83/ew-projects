<?php


namespace App\Exceptions\Localizations;


use Core\Exceptions\TranslatedException;

class TranslateExistsException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.localization.translate_exists'));
    }
}
