<?php

namespace App\Console\Commands\Syncs\BS;

use App\Dto\Users\UserSyncDto;
use App\Foundations\Modules\Permission\Models\Role;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Foundations\Modules\Permission\Roles\MechanicRole;
use App\Foundations\Modules\Permission\Roles\SuperAdminRole;
use App\Models\Users\User;
use App\Services\Requests\BaseHaulk\Commands\GetUsersCommand;
use App\Services\Users\UserSyncService;
use Illuminate\Console\Command;

class UsersSync extends Command
{
    protected $signature = 'sync:bs_users';

    protected int $count = 0;

    public function __construct(
        protected UserSyncService $service
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}], [count - {$this->count}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        $roles = Role::query()
            ->get()
            ->pluck('id', 'name')
            ->toArray();

        $bsRoles = [
            'BodyShopMechanic' => $roles[MechanicRole::NAME],
            'BodyShopAdmin'  => $roles[AdminRole::NAME],
            'BodyShopSuperAdmin'  => $roles[SuperAdminRole::NAME],
        ];

        /** @var $command GetUsersCommand */
        $command = resolve(GetUsersCommand::class);
        $results = $command->exec();

        foreach ($results as $item){
            $item['role_id'] = $bsRoles[$item['role']['name']];

            $dto = UserSyncDto::byArgs($item);

            if(!User::query()->withTrashed()->where('email', $dto->email)->exists()){
                $this->service->create($dto);
            }
            $this->count++;
        }
    }
}

