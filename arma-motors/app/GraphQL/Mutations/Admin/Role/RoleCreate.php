<?php

namespace App\GraphQL\Mutations\Admin\Role;

use App\DTO\Permission\RoleDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Services\Permission\RoleService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class RoleCreate extends BaseGraphQL
{
    public function __construct(
        protected RoleService $roleService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Role
     */
    public function __invoke($_, array $args): Role
    {
        $guard = \Auth::guard(Admin::GUARD)->user();

        try {
            $dto = RoleDTO::byArgs($args);
            $role = $this->roleService->create($dto);

            // @todo dev-telegram
            TelegramDev::info("Создана роль - {$dto->getName()}", $guard->name);

            return $role;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $guard->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

