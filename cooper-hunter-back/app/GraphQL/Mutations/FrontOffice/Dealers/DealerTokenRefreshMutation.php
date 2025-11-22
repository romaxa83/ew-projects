<?php

namespace App\GraphQL\Mutations\FrontOffice\Dealers;

use App\GraphQL\Types\Members\MemberLoginType;
use App\Services\Auth\DealerPassportService;
use Core\GraphQL\Mutations\BaseTokenRefreshMutation;
use GraphQL\Type\Definition\Type;

class DealerTokenRefreshMutation extends BaseTokenRefreshMutation
{
    public const NAME = 'dealerRefreshToken';

    public function __construct(
        protected DealerPassportService $passportService
    ) {
        $this->setDealerGuard();
    }

    public function type(): Type
    {
        return MemberLoginType::type();
    }
}
