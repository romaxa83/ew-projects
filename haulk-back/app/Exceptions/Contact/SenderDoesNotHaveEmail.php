<?php


namespace App\Exceptions\Contact;

use Exception;

class SenderDoesNotHaveEmail extends Exception
{

    public function __construct()
    {
        parent::__construct(trans("No sender email provided."));
    }
}
