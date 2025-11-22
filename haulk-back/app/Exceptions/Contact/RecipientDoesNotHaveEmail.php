<?php


namespace App\Exceptions\Contact;

use Exception;

class RecipientDoesNotHaveEmail extends Exception
{

    public function __construct()
    {
        parent::__construct(trans("No recipient email provided."));
    }
}
