<?php

namespace App\Listeners\User;

use App\Services\AA\RequestService;

class SendDataToUpdateUserListeners
{
    public function __construct(protected RequestService $requestService)
    {}

    public function handle($event)
    {
        try {
            if(isset($event->user) && (null !== $event->user)){
                $this->requestService->updateUser($event->user);
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}

