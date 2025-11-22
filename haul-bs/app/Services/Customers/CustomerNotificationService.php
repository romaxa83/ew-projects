<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Notifications\Customers\RegisterHaulkDepot;

class CustomerNotificationService
{
    public function __construct()
    {}

    public function registerInHaulkDepot(Customer $customer): void
    {}
}
