<?php

namespace App\Events\Listeners\Customers;

use App\Events\Events\Customers\CreateCustomerTaxExemptionEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Customer\CustomerTaxExemptionCreateCommand;

class SyncEComCreateCustomerTaxExemptionListener
{
    public function __construct()
    {}

    public function handle(CreateCustomerTaxExemptionEvent $event): void
    {
        try {
            /** @var $command CustomerTaxExemptionCreateCommand */
            $command = resolve(CustomerTaxExemptionCreateCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." Send tax exemption after CREATE [{$event->getModel()->name}] SUCCESS", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value." Send tax exemption after CREATE -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
