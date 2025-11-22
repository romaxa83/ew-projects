<?php

namespace App\Notifications;

use App\Models\Orders\Order;
use App\Notifications\Orders\SendDocs;
use App\Services\Fax\StatusHandleable;

class SendPdfBol extends SendDocs implements StatusHandleable
{

    public function __construct(Order $order, string $pdf)
    {
        parent::__construct(
            $order,
            [
                'BOL' => [
                    'data' => $pdf,
                    'name' => 'bol.pdf'
                ]
            ]
        );
    }

}
