<?php


namespace App\Exceptions\Order;

use Exception;

class OrderSignatureLinkExpired extends Exception
{

    public function __construct()
    {
        parent::__construct(trans("Your signature link has been expired."));
    }
}
