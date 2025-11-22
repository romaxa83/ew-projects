<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\Helpers;
use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string New()
 * @method static string In_process()
 * @method static string Sent()
 * @method static string Pending_pickup()
 * @method static string Delivered()
 * @method static string Canceled()
 * @method static string Returned()
 * @method static string Lost()
 * @method static string Damaged()
 */

enum OrderStatus: string {

    use InvokableCases;
    use RuleIn;
    use Label;
    use Helpers;

    case New = 'new';
    case In_process = 'in_process';
    case Sent = 'sent';
    case Pending_pickup = 'pending_pickup';
    case Delivered = 'delivered';
    case Canceled = 'canceled';
    case Returned = 'returned';
    case Lost = 'lost';
    case Damaged = 'damaged';

    public function isNew(): bool
    {
        return $this === self::New;
    }

    public function isInProcess(): bool
    {
        return $this === self::In_process;
    }

    public function isPendingPickup(): bool
    {
        return $this === self::Pending_pickup;
    }

    public function isSent(): bool
    {
        return $this === self::Sent;
    }

    public function isDelivered(): bool
    {
        return $this === self::Delivered;
    }

    public function isCanceled(): bool
    {
        return $this === self::Canceled;
    }

    public function isReturned(): bool
    {
        return $this === self::Returned;
    }

    public function statusForEdit(): bool
    {
        return $this === self::New || $this === self::In_process;
    }

    public function statusIsFinal(): bool
    {
        return $this === self::Damaged
            || $this === self::Lost
            || $this === self::Returned
            || $this === self::Canceled
            ;
    }

    public static function statusForNotTracking(): array
    {
        return [self::Damaged, self::Lost, self::Returned, self::Canceled, self::Delivered];
    }

    public function toggleOn(): array
    {
        $tmp = [
            self::New->value => [
                self::In_process,
                self::Canceled,
            ],
            self::In_process->value => [
                self::Pending_pickup,
                self::Canceled,
                self::Sent,
            ],
            self::Pending_pickup->value => [
                self::Delivered,
            ],
            self::Sent->value => [
                self::Delivered,
                self::Lost,
                self::Damaged,
            ],
            self::Delivered->value => [
                self::Returned,
            ],
        ];

        $res = [];
        foreach ($tmp[$this->value] ?? []  as $k => $enum) {
            $res[$k] = [
                'value' => $enum->value,
                'label' => $enum->label()
            ];
        }

        return $res;
    }
}
