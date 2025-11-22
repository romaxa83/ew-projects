<?php


namespace App\Exceptions\Order;

use Exception;

class EmptyInvoiceTotalDue extends Exception
{

    public function __construct(string $invoiceRecipient)
    {
        parent::__construct(trans("The invoice for " . $invoiceRecipient . " is empty."));
    }
}
