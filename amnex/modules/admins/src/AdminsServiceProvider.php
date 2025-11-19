<?php

declare(strict_types=1);

namespace Wezom\Admins;

use Gate;
use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rules\Password;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Wezom\Admins\Commands\MakeSuperAdminCommand;
use Wezom\Admins\Database\Seeders\AdminPermissionsSeeder;
use Wezom\Admins\Database\Seeders\SuperAdminRoleSeeder;
use Wezom\Admins\Dto\AdminDto;
use Wezom\Admins\Enums\AdminOrderColumnEnum;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Core\BaseServiceProvider;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Permissions\PermissionsManager;

class AdminsServiceProvider extends BaseServiceProvider
{
    protected array $morphMap = [Admin::class];
    protected array $graphQlEnums = [AdminStatusEnum::class, AdminOrderColumnEnum::class];
    protected array $graphQlInputs = [
        AdminDto::class,
    ];

    public function register()
    {
    }

    /**
     * @throws DefinitionException
     */
    public function boot(TypeRegistry $typeRegistry): void
    {
        parent::boot($typeRegistry);

        Password::defaults(
            fn () => Password::min(Admin::MIN_LENGTH_PASSWORD)
                ->max(Admin::MAX_LENGTH_PASSWORD)
                ->letters()
                ->numbers()
                ->symbols()
        );
    }

    public function permissions(PermissionsManager $permissions): void
    {
        Gate::before(static function (Authenticatable $user, $ability) {
            return (
                $user instanceof Admin
                && $user->isSuperAdmin()
                && (config('permissions.super_admin_ignores_policies') || str_contains($ability, '.'))
            ) ? true : null;
        });

        $permissions->add(Admin::class, 'admins::permissions.groups.admins');

        // This ability should not be given to ANY user!
        Gate::define(AdminConst::SUPER_ADMIN, function (Authenticatable $user) {
            return $user instanceof Admin && $user->isSuperAdmin();
        });
    }

    public function registerSeeders(): void
    {
        $this->seeding(AdminPermissionsSeeder::class);
        $this->seeding(SuperAdminRoleSeeder::class, fn () => Role::superAdmin()->doesntExist());
    }

    public function registerListeners(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        Event::listen('cms:install:after_migrate', function (Command $command) {
            if (
                Admin::query()->superAdmin()->doesntExist()
                && $command->confirm('Do you want to create a super admin?')
            ) {
                Artisan::call('make:super-admin');
            }
        });
    }

    public function registerCommands(): void
    {
        $this->commands([
            MakeSuperAdminCommand::class,
        ]);
    }
}
