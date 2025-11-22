<?php

namespace App\Listeners\Technicians;

use App\Events\Technicians\TechnicianUpdatedEvent;
use App\Models\Technicians\Technician;
use App\Services\Technicians\TechnicianService;
use Core\Chat\Facades\Chat;

class TechnicianUpdatedListener
{
    public function __construct(
        private TechnicianService $technicianService
    ) {}

    public function handle(TechnicianUpdatedEvent $event): void
    {
        $technician = $event->getTechnician();
        if ($technician->wasChanged('email')) {
            $this->technicianService->changeEmailByEvent($technician->refresh());
        }
        if ($technician->wasChanged('hvac_license') || $technician->wasChanged('epa_license')) {
            $this->technicianService->setReModeration($technician->refresh());
        }
        if ($technician->wasChanged('first_name', 'last_name')) {
            $this->handleConversationName($technician);
        }
    }

    protected function handleConversationName(Technician $technician): void
    {
        $conversation = Chat::conversations()
            ->getQueryForUser($technician)->first();

        if (!$conversation) {
            return;
        }

        $conversation->title = $technician->getName();
        $conversation->save();
    }
}
