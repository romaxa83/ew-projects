<?php

namespace App\Enums\Clients;

use App\Enums\Base\InvokableCases;

/**
 * @method static Customer_inventory_save()
 */

enum ActivityType: string {

    use InvokableCases;

    case Customer_inventory_save = "customer.inventory.save";
}

