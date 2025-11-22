<?php

namespace App\Events\Listeners\Customers;

use App\Events\Events\Customers\AcceptedCustomerTaxExemptionEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Customer\CustomerTaxExemptionAcceptedCommand;

class SyncEComAcceptedCustomerTaxExemptionListener
{
    public function __construct()
    {}

    public function handle(AcceptedCustomerTaxExemptionEvent $event): void
    {
        try {
            /** @var $command CustomerTaxExemptionAcceptedCommand */
            $command = resolve(CustomerTaxExemptionAcceptedCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." Accepted tax exemption after CREATE [{$event->getModel()->name}] SUCCESS", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value." Accepted tax exemption after CREATE -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
