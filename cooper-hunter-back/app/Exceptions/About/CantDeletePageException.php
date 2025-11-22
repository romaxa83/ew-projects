<?php

namespace App\Exceptions\About;

use Core\Exceptions\TranslatedException;

class CantDeletePageException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.about.page.cant_delete'));
    }
}
