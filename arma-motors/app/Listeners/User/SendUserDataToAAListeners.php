<?php

namespace App\Listeners\User;

use App\Events\User\NotUserFromAA;
use App\Services\AA\RequestService;

class SendUserDataToAAListeners
{
    public function __construct(protected RequestService $requestService)
    {}

    public function handle(NotUserFromAA $event)
    {
        try {


          $this->requestService->createUser($event->user);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
