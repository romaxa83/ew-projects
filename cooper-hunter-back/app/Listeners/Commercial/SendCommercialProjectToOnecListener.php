<?php

namespace App\Listeners\Commercial;

use App\Events\Commercial\SendCommercialProjectToOnec;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCommercialProjectToOnecListener implements ShouldQueue
{
    public function handle(SendCommercialProjectToOnec $event): void
    {
        try {
            /** @var $service RequestService */
            $service = resolve(RequestService::class);
            if($event->getCommercialProject()->guid){
                $service->updateCommercialProject($event->getCommercialProject());
            } else {
                $service->createCommercialProject($event->getCommercialProject());
            }

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}

