<?php

declare(strict_types=1);

namespace Wezom\Core;

use Gate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Http\Request;
use Laravel\Horizon\Horizon;
use Laravel\Sanctum\Sanctum;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\RequestWatcher;
use Nuwave\Lighthouse\Cache\CacheKeyAndTags;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Schema\Types\Scalars\Upload;
use Str;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Commands\Auth\PruneExpiredAuthTokens;
use Wezom\Core\Commands\Auth\PruneExpiredGuestSessions;
use Wezom\Core\Commands\InstallCommand;
use Wezom\Core\Contracts\Permissions\PermissionsContainer;
use Wezom\Core\Dto\DateRangeDto;
use Wezom\Core\Dto\DateTimeRangeDto;
use Wezom\Core\Dto\TranslationDto;
use Wezom\Core\Enums\Images\ImageSizeEnum;
use Wezom\Core\Enums\Messages\MessageTypeEnum;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Enums\RoleOrderColumnEnum;
use Wezom\Core\Enums\TranslationOrderColumnEnum;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\ExtendPackage\Lighthouse\CacheKeyAndTagsGenerator;
use Wezom\Core\ExtendPackage\Lighthouse\TypeRegistry as WezomTypeRegistry;
use Wezom\Core\ExtendPackage\Macro\BlueprintMacro;
use Wezom\Core\ExtendPackage\Macro\TestResponseMacro;
use Wezom\Core\Foundation\RouteRegistrar;
use Wezom\Core\GraphQL\Types\DateForFront;
use Wezom\Core\GraphQL\Types\DateTimeForFront;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Media;
use Wezom\Core\Models\Permission\Permission;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Models\Translation;
use Wezom\Core\Permissions\Permissions;
use Wezom\Core\Permissions\PermissionsManager;
use Wezom\Core\Services\GraphQlTypeGeneratingService;
use Wezom\Core\Services\Localizations\LocalizationService;
use Wezom\Users\UsersServiceProvider;

class CoreServiceProvider extends BaseServiceProvider
{
    protected array $graphQlEnums = [
        MessageTypeEnum::class,
        ImageSizeEnum::class,
        OrderDirectionEnum::class,
        TranslationSideEnum::class,
        RoleOrderColumnEnum::class,
        TranslationOrderColumnEnum::class,
    ];
    protected array $graphQlTypes = [
        DateForFront::class,
        DateTimeForFront::class,
        Upload::class,
    ];
    protected array $graphQlInputs = [
        TranslationDto::class,
        DateTimeRangeDto::class,
        DateRangeDto::class,
    ];

    public function register(): void
    {
        $this->app->singleton(TypeRegistry::class, WezomTypeRegistry::class);

        Telescope::night();
        Telescope::auth(function (Request $request) {
            if (config('telescope.ip_restriction')) {
                return in_array($request->ip(), config('telescope.allowed_ips'), true);
            }

            return $this->app->isLocal();
        });

        Horizon::auth(function (Request $request) {
            if (config('horizon.ip_restriction')) {
                return in_array($request->ip(), config('horizon.allowed_ips'), true);
            }

            return $this->app->isLocal();
        });

        if (!$this->app->configurationIsCached()) {
            //unset base laravel auth config to configure them by admin [and user] module[s]
            $authConfig = [];
            if (!class_exists(UsersServiceProvider::class)) {
                $authConfig['defaults'] = ['guard' => Admin::GUARD];
            }
            $this->app['config']->set('auth', $authConfig);
            $this->app['config']->set('permission.models.permission', Permission::class);
            $this->app['config']->set('permission.models.role', Role::class);
            $this->app['config']->set('media-library.media_model', Media::class);
        }

        $this->app->bind(RequestWatcher::class, Services\Telescope\Watchers\RequestWatcher::class);
        $this->app->singleton(
            'localization',
            fn (Application $app) => $app->make(LocalizationService::class)
        );

        EloquentFactory::guessFactoryNamesUsing(static function (string $modelName): string {
            return Str::of($modelName)
                ->replaceFirst('\\Models\\', '\\Database\\Factories\\')
                ->append('Factory')
                ->toString();
        });

        EloquentFactory::guessModelNamesUsing(static function (EloquentFactory $factory): string {
            return Str::of($factory::class)
                ->replace('\\Database\\Factories\\', '\\Models\\')
                ->replaceLast('Factory', '')
                ->toString();
        });

        Gate::guessPolicyNamesUsing(static function (string $modelClass): string {
            return Str::of($modelClass)
                ->replace('\\Models\\', '\\Policies\\')
                ->append('Policy')
                ->toString();
        });

        $this->app->singleton(RouteRegistrar::class, fn () => new RouteRegistrar());
        $this->app->singleton(PermissionsManager::class);
        $this->app->bind(
            PermissionsContainer::class,
            fn (Application $app, array $args) => new Permissions($args[0] ?? null)
        );
        $this->app->bind(CacheKeyAndTags::class, CacheKeyAndTagsGenerator::class);

        $this->app->singleton(GraphQlTypeGeneratingService::class);

        $this->registerMacros();

        $this->setUpSanctum();
    }

    private function setUpSanctum(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    protected function config(): void
    {
        parent::config();

        if ($this->app->configurationIsCached()) {
            return;
        }

        $config = $this->app['config'];

        // Override email templates
        $config->set('mail.markdown.theme', 'wezom');
        $config->prepend('mail.markdown.paths', $this->root('resources/views/mail'));

        // Override notifications template
        $this->callAfterResolving('view', function ($view) {
            $view->prependNamespace('notifications', $this->root('resources/views/mail/notifications'));
        });

        $config->set('lighthouse.pagination.default_count', $config->get('app.default_pagination_count'));
    }

    public function permissions(PermissionsManager $permissions): void
    {
        $permissions->add(Role::class, 'core::permissions.groups.roles');
        $permissions->add(Translation::class, 'core::permissions.groups.translations');
    }

    /**
     * Register console commands.
     */
    public function registerCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            PruneExpiredAuthTokens::class,
            PruneExpiredGuestSessions::class,
        ]);
    }

    public function jobs(Schedule $schedule): void
    {
        if (config('telescope.enabled')) {
            $schedule->command('telescope:prune')->twiceDaily();
        }
        $schedule->job(PruneExpiredAuthTokens::class)->hourly();
        $schedule->job(PruneExpiredGuestSessions::class)->daily();
    }

    private function registerMacros(): void
    {
        BlueprintMacro::register();
        TestResponseMacro::register();
    }
}
