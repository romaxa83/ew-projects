<?php

namespace App\Listeners\Commercial;

use App\Events\Commercial\SendCommercialProjectToOnec;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteCommercialProjectToOnecListener implements ShouldQueue
{
    public function handle(SendCommercialProjectToOnec $event): void
    {
        $service = app(RequestService::class);

        try {
            $service->deleteCommercialProject($event->getCommercialProject());

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}


