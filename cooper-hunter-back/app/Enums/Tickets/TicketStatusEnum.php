<?php

namespace App\Enums\Tickets;

use Core\Enums\BaseEnum;

/**
 * @method static static NEW()
 *
 * @method static static DONE()
 * @method static static PENDING()
 * @method static static PENDING_PAYMENT()
 * @method static static PENDING_SHIPMENT()
 * @method static static EXCHANGE()
 */
class TicketStatusEnum extends BaseEnum
{
    public const DONE = 'done';
    public const PENDING = 'pending';
    public const PENDING_PAYMENT = 'pending_payment';
    public const PENDING_SHIPMENT = 'pending_shipment';
    public const EXCHANGE = 'exchange';

    /*
     * Created by technician
     */
    public const NEW = 'new';

    public function updatable(): bool
    {
        return $this->is(self::NEW());
    }

    public function orderable(): bool
    {
        return $this->in(self::getOrderableValues());
    }

    public static function getOrderableValues(): array
    {
        //Not sure about all the statuses that make the ticket orderable
        return [
            self::NEW,
            self::PENDING,
        ];
    }
}
