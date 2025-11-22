<?php

namespace App\Listeners\Order;

use App\Events\Order\AcceptAgreementEvent;
use App\Services\AA\RequestService;

class AcceptAgreementListeners
{
    public function __construct(protected RequestService $requestService)
    {}

    public function handle(AcceptAgreementEvent $event)
    {
        try {
            if(isset($event->model) && (null !== $event->model)){
                $this->requestService->acceptAgreement($event->model);
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
