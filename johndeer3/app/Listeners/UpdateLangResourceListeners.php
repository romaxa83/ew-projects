<?php

namespace App\Listeners;

use App\Events\UpdateSysTranslations;
use App\Repositories\TranslationRepository;
use App\Services\Translations\TransferService;

class UpdateLangResourceListeners
{
    public function __construct(
        protected TranslationRepository $repo,
        protected TransferService $service
    )
    {}

    public function handle(UpdateSysTranslations $event)
    {
        try {
            $this->service->updateLangResource(
                $this->repo->getObjByIDs($event->ids)
            );
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
