<?php

namespace App\Listeners\Companies;

use App\Events\Companies\UpdateCompanyByOnecEvent;
use App\Services\Companies\CompanyService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCodeForDealerListener implements ShouldQueue
{
    public function __construct(protected CompanyService $service)
    {}

    public function handle(UpdateCompanyByOnecEvent $event): void
    {
        $this->service->sendCode($event->getCompany());
    }
}
