<?php

namespace App\Events\Commercial;

use App\Models\Commercial\RDPAccount;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RDPCredentialsGeneratedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected RDPAccount $account)
    {
    }

    public function getAccount(): RDPAccount
    {
        return $this->account;
    }
}