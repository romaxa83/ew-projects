<?php

namespace App\GraphQL\Mutations\User\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\AA\RequestService;
use App\Services\Auth\UserPassportService;
use App\Services\User\UserService;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class UserDeleteAccount extends BaseGraphQL
{
    public function __construct(
        protected UserService $service,
        protected UserPassportService $passportService,
        protected RequestService $requestService,
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        /** @var $user User*/
        $user = Auth::guard(User::GUARD)->user();
        try {
            if($user->uuid){
                $this->requestService->deleteUser($user);
            }

            $this->service->delete($user);

            $this->passportService->logout($user);

            return $this->successResponse(__('message.user.delete account'));
        } catch (\Throwable $e) {
            $this->throwExceptionError($e);
        }
    }
}
