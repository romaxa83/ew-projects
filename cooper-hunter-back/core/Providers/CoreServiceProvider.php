<?php

declare(strict_types=1);

namespace Core\Providers;

use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Providers\TelescopeServiceProvider;
use App\Services\Localizations\LocaleService;
use App\Services\Localizations\LocalizationService;
use Core\Console\Commands\GenerateCrudPermissionCommand;
use Core\Console\Commands\GenerateModelFilterCommand;
use Core\Console\Commands\GeneratorDtoCommand;
use Core\Console\Commands\GeneratorFactoryCommand;
use Core\Console\Commands\GeneratorModelCommand;
use Core\Console\Commands\GeneratorModuleCommand;
use Core\Console\Commands\GeneratorPermissionGroupCommand;
use Core\Console\Commands\GeneratorServiceCommand;
use Core\Console\Commands\ModuleGeneratorGraphQLCRUDCommand;
use Core\Console\Commands\ModuleGeneratorGraphQLDeleteCommand;
use Core\Console\Commands\ModuleGeneratorGraphQLQueryCommand;
use Core\Console\Commands\ModuleGeneratorGraphQLTypeCommand;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    protected array $commands = [
        GeneratorModuleCommand::class,
        GeneratorPermissionGroupCommand::class,
        GenerateCrudPermissionCommand::class,
        ModuleGeneratorGraphQLTypeCommand::class,
        ModuleGeneratorGraphQLQueryCommand::class,
        ModuleGeneratorGraphQLCRUDCommand::class,
        ModuleGeneratorGraphQLDeleteCommand::class,
        GeneratorServiceCommand::class,
        GeneratorModelCommand::class,
        GenerateModelFilterCommand::class,
        GeneratorFactoryCommand::class,
        GeneratorDtoCommand::class,
    ];

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerConsole();
        }

        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);

        $this->app->bind(LocalizationService::class, LocalizationService::class);

        $this->app->singleton(
            'localization',
            fn(Application $app) => $app->make(LocalizationService::class)
        );

        $this->app->singleton(
            'locales',
            fn(Application $app) => $app->make(LocaleService::class)
        );

        $this->registerMacro();
    }

    protected function registerMacro(): void
    {
    }

    public function boot(): void
    {
        $this->allowAllToSuperAdminRole();
        $this->registerMorphMap();
    }

    protected function allowAllToSuperAdminRole(): void
    {
        Gate::before(
            static fn($user, $ability) => $user instanceof Admin
            && $user->hasRole(config('permission.roles.super_admin')) ? true : null
        );
    }

    public function registerConsole(): void
    {
        $this->commands($this->commands);
    }

    protected function registerMorphMap(): void
    {
        Relation::morphMap(
            [
                Admin::MORPH_NAME => Admin::class,
                User::MORPH_NAME => User::class,
            ]
        );
    }
}
