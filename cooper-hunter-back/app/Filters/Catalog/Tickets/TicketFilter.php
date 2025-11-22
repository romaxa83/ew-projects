<?php

namespace App\Filters\Catalog\Tickets;

use App\Filters\BaseModelFilter;
use App\Models\Catalog\Tickets\Ticket;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin Ticket
 */
class TicketFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function serialNumber(string $serialNumber): void
    {
        $this->where('serial_number', $serialNumber);
    }
}
