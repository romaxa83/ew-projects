<?php


namespace App\Broadcasting\Events\Payroll;

class PayrollCreateBroadcast extends PayrollBroadcast
{
    public const NAME = 'payroll.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
