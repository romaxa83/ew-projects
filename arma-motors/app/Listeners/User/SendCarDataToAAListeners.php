<?php

namespace App\Listeners\User;

use App\Events\User\SendCarDataToAA;
use App\Services\AA\RequestService;

class SendCarDataToAAListeners
{
    public function __construct(protected RequestService $requestService)
    {}

    public function handle(SendCarDataToAA $event)
    {
        try {
            if(isset($event->car) && !empty($event->car)){
                $this->requestService->createCar($event->car);
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
