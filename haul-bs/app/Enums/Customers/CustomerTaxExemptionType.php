<?php

declare(strict_types=1);

namespace App\Enums\Customers;

enum CustomerTaxExemptionType: string
{
    case ECOM = 'ECOM';
    case BODY = 'BODY';
}
