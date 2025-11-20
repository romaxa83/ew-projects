<?php

namespace App\GraphQL\Mutations\BackOffice\Auth\Admin;

use App\GraphQL\Types\Auth\LoginTokenType;
use App\Models\Admins\Admin;
use App\Services\Auth\AdminPassportService;
use Core\GraphQL\Mutations\BaseTokenRefreshMutation;
use Core\Services\Auth\AuthPassportService;
use GraphQL\Type\Definition\Type;

class AdminTokenRefreshMutation extends BaseTokenRefreshMutation
{
    public const NAME = 'AdminRefreshToken';

    public function __construct(
        protected AdminPassportService $passportService
    ) {
    }

    protected function getPassportService(): AuthPassportService
    {
        return $this->passportService;
    }

    protected function getGuard(): string
    {
        return Admin::GUARD;
    }

    public function type(): Type
    {
        return LoginTokenType::type();
    }
}
