<?php

namespace App\Notifications;

use App\Models\Orders\Order;
use App\Notifications\Orders\SendDocs;
use App\Services\Fax\StatusHandleable;

class SendPdfInvoice extends SendDocs implements StatusHandleable
{

    public function __construct(Order $order, $pdf)
    {
        parent::__construct(
            $order,
            [
                'invoice' => [
                    'data' => $pdf,
                    'name' => 'invoice.pdf'
                ]
            ]
        );
    }
}
