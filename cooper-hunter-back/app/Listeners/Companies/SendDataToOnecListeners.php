<?php

namespace App\Listeners\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDataToOnecListeners implements ShouldQueue
{
    public function handle(CreateOrUpdateCompanyEvent $event): void
    {
        try {
            /** @var $service RequestService */
            $service = resolve(RequestService::class);
            if($event->getCompany()->guid){
                $service->updateCompany($event->getCompany());
            } else {
                $service->createCompany($event->getCompany());
            }

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}
