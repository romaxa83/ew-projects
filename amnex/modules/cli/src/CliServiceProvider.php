<?php

declare(strict_types=1);

namespace Wezom\Cli;

use Illuminate\Database\Migrations\MigrationCreator;
use Wezom\Cli\Commands\CrudMakeCommand;
use Wezom\Cli\Commands\FactoryMakeCommand;
use Wezom\Cli\Commands\FilterMakeCommand;
use Wezom\Cli\Commands\ModelMakeCommand;
use Wezom\Cli\Commands\ModuleMakeCommand;
use Wezom\Cli\Commands\SettingsMakeCommand;
use Wezom\Cli\Support\IdeHelper\ModelHook;
use Wezom\Core\BaseServiceProvider;

class CliServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->extendMigrationCreator();
    }

    protected function config(): void
    {
        parent::config();

        $config = $this->app['config'];

        if ($this->app->configurationIsCached() || $config->has('ide-helper')) {
            return;
        }

        $config->set(
            'ide-helper.model_hooks',
            array_merge($config->get('ide-helper.model_hooks', []), [ModelHook::class])
        );
    }

    public function registerCommands(): void
    {
        $this->commands([
            ModuleMakeCommand::class,
            ModelMakeCommand::class,
            FactoryMakeCommand::class,
            FilterMakeCommand::class,
            SettingsMakeCommand::class,
            CrudMakeCommand::class,
        ]);
    }

    protected function extendMigrationCreator(): void
    {
        $this->app->extend('migration.creator', function (MigrationCreator $creator) {
            return new MigrationCreator(
                $creator->getFilesystem(),
                __DIR__ . '/Commands/stubs/model'
            );
        });
    }
}
