<?php

namespace App\Events\Listeners\Customers;

use App\Events\Events\Customers\DeleteCustomerTaxExemptionEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Customer\CustomerTaxExemptionDeleteCommand;

class SyncEComDeleteCustomerTaxExemptionListener
{
    public function __construct()
    {}

    public function handle(DeleteCustomerTaxExemptionEvent $event): void
    {
        try {
            /** @var $command CustomerTaxExemptionDeleteCommand */
            $command = resolve(CustomerTaxExemptionDeleteCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." Delete tax exemption  [{$event->getModel()->name}] SUCCESS", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value." Delete tax exemption -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
