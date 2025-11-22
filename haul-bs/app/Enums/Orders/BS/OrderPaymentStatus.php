<?php

namespace App\Enums\Orders\BS;

enum OrderPaymentStatus: string {

    case Paid = 'paid';
    case Not_paid = 'not_paid';
    case Billed= 'billed';
    case Not_billed = 'not_billed';
    case Overdue = 'overdue';
    case Not_overdue= 'not_overdue';
}
