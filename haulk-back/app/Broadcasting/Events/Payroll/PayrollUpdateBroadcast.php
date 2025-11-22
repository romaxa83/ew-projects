<?php


namespace App\Broadcasting\Events\Payroll;

class PayrollUpdateBroadcast extends PayrollBroadcast
{
    public const NAME = 'payroll.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
