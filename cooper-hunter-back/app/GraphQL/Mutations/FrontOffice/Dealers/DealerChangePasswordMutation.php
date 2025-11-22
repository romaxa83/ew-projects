<?php

namespace App\GraphQL\Mutations\FrontOffice\Dealers;

use App\Services\Dealers\DealerService;
use Core\GraphQL\Mutations\BaseChangePasswordMutation;

class DealerChangePasswordMutation extends BaseChangePasswordMutation
{
    public const NAME = 'dealerChangePassword';

    public function __construct(protected DealerService $service)
    {
        $this->setDealerGuard();
    }
}
