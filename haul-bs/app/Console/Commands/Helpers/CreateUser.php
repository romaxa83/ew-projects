<?php

namespace App\Console\Commands\Helpers;


use App\Dto\Users\UserDto;
use App\Foundations\Modules\Permission\Models\Role;
use App\Foundations\Modules\Permission\Roles\DefaultRole;
use App\Foundations\Modules\Permission\Roles\SuperAdminRole;
use App\Services\Users\UserService;
use Illuminate\Console\Command;

class CreateUser extends Command
{
    protected $signature = 'helpers:create_user';

    public function __construct(
        protected UserService $service
    )
    {
        parent::__construct();
    }

    public function handle()
    {

        /** @var DefaultRole $defaultRoleSuperAdmin */
        $defaultRoleSuperAdmin = resolve(SuperAdminRole::class);
        $role = Role::query()
            ->where('name', $defaultRoleSuperAdmin->getName())
            ->where('guard_name', $defaultRoleSuperAdmin->getGuard())
            ->first();


        $dto = UserDto::byArgs([
            'name' => $this->ask('name'),
            'email' => $this->ask('email'),
            'password' => $this->ask('password'),
            'role_id' => $role->id,
        ]);

        try {
            $start = microtime(true);

            $this->exec($dto);

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec(UserDto $dto)
    {
        $this->service->create($dto);
    }
}
