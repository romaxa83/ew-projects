<?php

namespace App\Listeners\Employees;

use App\Events\Employees\EmployeeCreatedEvent;
use App\Services\Employees\EmployeeService;
use Exception;

class SendCredentialsListener
{
    public function __construct(protected EmployeeService $service)
    {}

    /**
     * @throws Exception
     */
    public function handle(EmployeeCreatedEvent $event): void
    {
        try {
            if($event->getDto() && $event->getDto()->sendEmail){
                $this->service->sendCredentialsNotification($event->getModel(), $event->getDto()->password);
            } else {
                logger_info("NO SEND email for credentials to employee [{$event->getModel()->email->getValue()}], no dto or sendEmail - false");
            }

        } catch (\Throwable $e) {
            logger_info("Send email [SendCredentialsListener] FAILED", [
                'message' => $e->getMessage()
            ]);
        }
    }
}

