<?php

declare(strict_types=1);

namespace App\Enums\Customers;

enum CustomerTaxExemptionStatus: string
{
    case NOT_SEND = 'NOT_SEND';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case ACCEPTED = 'ACCEPTED';
    case DECLINED = 'DECLINED';

    case EXPIRED = 'EXPIRED';
}
