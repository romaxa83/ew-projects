<?php


namespace App\Broadcasting\Events\Payroll;

class PayrollMarkIsPaidBroadcast extends PayrollBroadcast
{
    public const NAME = 'payroll.mark-is-paid';

    protected function getName(): string
    {
        return self::NAME;
    }
}
