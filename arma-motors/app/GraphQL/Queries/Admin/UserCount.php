<?php

namespace App\GraphQL\Queries\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\User\UserRepository;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;

class UserCount extends BaseGraphQL
{
    use GraphqlResponse;

    public function __construct(
        protected UserRepository $userRepository,
    ){}
    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Admin
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        /** @var $admin Admin */
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            return [
                "key" => "count",
                "name" => $this->userRepository->countBy('status', $args['status'] ?? null),
            ];
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $admin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


