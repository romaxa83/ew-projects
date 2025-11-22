<?php

namespace App\Events\Listeners\Customers;

use App\Events\Events\Customers\DeclineCustomerTaxExemptionEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Customer\CustomerTaxExemptionDeclineCommand;

class SyncEComDeclineCustomerTaxExemptionListener
{
    public function __construct()
    {}

    public function handle(DeclineCustomerTaxExemptionEvent $event): void
    {
        try {
            /** @var $command CustomerTaxExemptionDeclineCommand */
            $command = resolve(CustomerTaxExemptionDeclineCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." Decline tax exemption  [{$event->getModel()->name}] SUCCESS", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value." Decline tax exemption -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
