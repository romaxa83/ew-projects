<?php

namespace App\GraphQL\Mutations\FrontOffice\Members;

use App\GraphQL\Types\Members\MemberLoginType;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Rules\LoginDealer;
use App\Rules\LoginTechnician;
use App\Rules\LoginUser;
use App\Services\Auth\DealerPassportService;
use App\Services\Auth\TechnicianPassportService;
use App\Services\Auth\UserPassportService;
use App\Traits\Auth\CanRememberMe;
use Core\GraphQL\Mutations\BaseLoginMutation;
use GraphQL\Type\Definition\Type;

class MemberLoginMutation extends BaseLoginMutation
{
    use CanRememberMe;

    public const NAME = 'memberLogin';

    public function __construct(
        protected TechnicianPassportService $technicianPassportService,
        protected UserPassportService $userPassportService,
        protected DealerPassportService $dealerPassportService,
    ) {
    }

    public function type(): Type
    {
        return MemberLoginType::type();
    }

    protected function rules(array $args = []): array
    {
        if (Technician::query()->where('email', $args['username'])->exists()) {
            $this->setTechnicianGuard();
            $rule = new LoginTechnician($args);
        } elseif (Dealer::query()->where('email', $args['username'])->exists()) {
            $this->setDealerGuard();
            $rule = new LoginDealer($args);
        } else {
            $this->setUserGuard();
            $rule = new LoginUser($args);
        }

        $rules = parent::rules($args);

        $rules['password'] = ['required', 'string', 'min:8', $rule];

        return $rules;
    }

    protected function getPassportService()
    : TechnicianPassportService|UserPassportService|DealerPassportService
    {
        return match ($this->guard) {
            User::GUARD => $this->userPassportService,
            Technician::GUARD => $this->technicianPassportService,
            Dealer::GUARD => $this->dealerPassportService,
        };
    }
}
