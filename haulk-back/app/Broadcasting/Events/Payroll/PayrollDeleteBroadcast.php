<?php


namespace App\Broadcasting\Events\Payroll;

class PayrollDeleteBroadcast extends PayrollBroadcast
{
    public const NAME = 'payroll.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
