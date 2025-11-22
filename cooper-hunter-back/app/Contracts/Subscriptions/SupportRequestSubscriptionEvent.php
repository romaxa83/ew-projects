<?php


namespace App\Contracts\Subscriptions;


use App\Models\Admins\Admin;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;

interface SupportRequestSubscriptionEvent
{
    public function getSupportRequest(): SupportRequest;

    public function getSender(): Technician|Admin;

    public function getAction(): ?string;
}
