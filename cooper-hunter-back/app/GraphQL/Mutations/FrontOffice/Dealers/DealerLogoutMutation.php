<?php

namespace App\GraphQL\Mutations\FrontOffice\Dealers;

use App\Services\Auth\DealerPassportService;
use Core\GraphQL\Mutations\BaseLogoutMutation;

class DealerLogoutMutation extends BaseLogoutMutation
{
    public const NAME = 'dealerLogout';

    public function __construct(
        protected DealerPassportService $passportService
    ) {
        $this->setDealerGuard();
    }
}
