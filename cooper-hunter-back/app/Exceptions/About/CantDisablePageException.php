<?php

namespace App\Exceptions\About;

use Core\Exceptions\TranslatedException;

class CantDisablePageException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.about.page.cant_disable'));
    }
}
