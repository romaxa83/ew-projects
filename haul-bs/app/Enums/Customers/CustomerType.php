<?php

namespace App\Enums\Customers;

use App\Foundations\Enums\Traits\InvokableCases;

enum CustomerType: string {

    use InvokableCases;

    case BS = "bs";         // созданы в этом приложении
    case EComm = "ecomm";   // полученные из e-commerce
    case Haulk = "haulk";   // полученные из haulk (при связи с техникой)

    public function isHaulk(): bool
    {
        return $this === self::Haulk;
    }

    public function isBS(): bool
    {
        return $this === self::BS;
    }

    public function isEComm(): bool
    {
        return $this === self::EComm;
    }
}
