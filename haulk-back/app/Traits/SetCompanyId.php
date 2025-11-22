<?php

namespace App\Traits;

trait SetCompanyId
{
    public function setCompanyId(): void
    {
        if ($user = authUser()) {
            if ($user->isBroker()) {
                $this->broker_id = $user->broker_id;
                return;
            }

            if ($user->isCarrier()) {
                $this->carrier_id = $user->carrier_id;
                return;
            }
        }
    }
}
