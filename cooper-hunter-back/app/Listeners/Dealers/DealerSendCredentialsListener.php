<?php

namespace App\Listeners\Dealers;

use App\Events\Dealers\CreateOrUpdateDealerEvent;
use App\Services\Dealers\DealerService;

class DealerSendCredentialsListener
{
    public function __construct(
        protected DealerService $service
    )
    {}

    public function handle(CreateOrUpdateDealerEvent $event): void
    {
        if($event->getDealerDto()){
            $this->service->sendCredentials($event->getDealerDto());
        }
    }
}
