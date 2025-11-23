<?php

namespace WezomCms\Core;

use Config;
use Exception;
use Form;
use Gate;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lang;
use Lavary\Menu\Builder;
use Livewire\Commands\ComponentParser;
use Livewire\LifecycleManager;
use Livewire\LivewireComponentsFinder;
use Livewire\WithPagination;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Menu;
use Request;
use SidebarMenu;
use WezomCms\Core\Commands\CreateSuperAdminCommand;
use WezomCms\Core\Commands\DeleteLostImagesCommand;
use WezomCms\Core\Commands\DownCommand;
use WezomCms\Core\Commands\InstallCommand;
use WezomCms\Core\Commands\MakeWebPCommand;
use WezomCms\Core\Commands\ReCropImagesCommand;
use WezomCms\Core\Commands\ReCropSettingImagesCommand;
use WezomCms\Core\Commands\Translations\DeleteUnusedCommand;
use WezomCms\Core\Commands\Translations\FindMissingCommand;
use WezomCms\Core\Commands\Translations\FreshCommand;
use WezomCms\Core\Commands\Translations\MissingDefaultCommand;
use WezomCms\Core\Commands\Translations\ScanCommand;
use WezomCms\Core\Commands\Translations\TranslateCommand;
use WezomCms\Core\Commands\WidgetsCommand;
use WezomCms\Core\Contracts\AdminPageNameInterface;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Contracts\BreadcrumbsInterface;
use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Contracts\Filter\FilterStateStorageInterface;
use WezomCms\Core\Contracts\Filter\RestoreFilterInterface;
use WezomCms\Core\Contracts\NavBar\NavBarInterface;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SettingsInterface;
use WezomCms\Core\Contracts\TranslationStorageInterface;
use WezomCms\Core\Enums\SeoFields;
use WezomCms\Core\Enums\TranslationSide;
use WezomCms\Core\ExceptionHandler as WezomExceptionHandler;
use WezomCms\Core\ExtendPackage\FormBuilder;
use WezomCms\Core\ExtendPackage\LaravelLocalization;
use WezomCms\Core\ExtendPackage\Livewire\LivewireComponentsFinder as WezomLivewireComponentsFinder;
use WezomCms\Core\ExtendPackage\Livewire\LivewireManager;
use WezomCms\Core\ExtendPackage\SEOTools\OpenGraph;
use WezomCms\Core\ExtendPackage\SEOTools\SEOMeta;
use WezomCms\Core\ExtendPackage\SEOTools\SEOTools;
use WezomCms\Core\ExtendPackage\SEOTools\TwitterCards;
use WezomCms\Core\Filter\CookieStateStorage;
use WezomCms\Core\Filter\RestoreFilter;
use WezomCms\Core\Foundation\Assets\WezomAssetManager;
use WezomCms\Core\Foundation\Breadcrumbs;
use WezomCms\Core\Foundation\Buttons\ButtonsContainer;
use WezomCms\Core\Foundation\Dashboard\DashboardContainer;
use WezomCms\Core\Foundation\DatabaseTranslationStorage;
use WezomCms\Core\Foundation\Money;
use WezomCms\Core\Foundation\NavBar\NavBar;
use WezomCms\Core\Foundation\Notifications\Drivers\SwalDriver;
use WezomCms\Core\Foundation\Notifications\FlashNotification;
use WezomCms\Core\Foundation\Notifications\NotifyDriverInterface;
use WezomCms\Core\Foundation\Permissions;
use WezomCms\Core\Foundation\RouteRegistrar;
use WezomCms\Core\Foundation\WezomAdminPageName;
use WezomCms\Core\Foundation\Widgets\Widget;
use WezomCms\Core\Http\Middleware\FormThrottleRequest;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Listeners\ClearWidgetsCache;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Models\Translation;
use WezomCms\Core\NavBarItems\FormButtons;
use WezomCms\Core\NavBarItems\LanguageSwitcher;
use WezomCms\Core\NavBarItems\Notifications;
use WezomCms\Core\Settings\Container as SettingsContainer;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;
use WezomCms\Core\ViewComposers\FooterComposer;
use WezomCms\Core\ViewComposers\HeadComposer;
use WezomCms\Core\ViewComposers\HeaderComposer;
use WezomCms\Core\ViewComposers\PageTitleComposer;
use WezomCms\Core\ViewComposers\SidebarComposer;

class CoreServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * All module widgets.
     *
     * @var array|string|null
     */
    protected $widgets = 'cms.core.widgets';

    /**
     * Custom translation keys.
     *
     * @var array
     */
    protected $translationKeys = [
        'cms-core::admin.auth.passwords.password',
        'cms-core::admin.auth.passwords.reset',
        'cms-core::admin.auth.passwords.sent',
        'cms-core::admin.auth.passwords.token',
        'cms-core::admin.auth.passwords.user',
        'cms-core::admin.translation_side.admin',
        'cms-core::admin.translation_side.site',
        'cms-core::site.For security reasons your request has been canceled Please try again later',
        'cms-core::admin.Phone is entered incorrectly',
        'cms-core::site.Phone is entered incorrectly',
        'cms-core::admin.or',
        'cms-core::site.or',
        'cms-core::admin.currency_symbol',
        'cms-core::site.currency_symbol',
    ];

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'eloquent.saved:*' => [ClearWidgetsCache::class],
        'eloquent.deleted:*' => [ClearWidgetsCache::class],
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('isBackend', function () {
            $prefix = config('app.admin_prefix', 'wezom');

            return Request::is($prefix, $prefix . '/*');
        });

        $this->app->singleton('side', function () {
            return $this->app['isBackend'] ? 'admin' : 'site';
        });

        $this->app->singleton(ImageService::class);

        $this->app->singleton(SettingsInterface::class, function ($app) {
            return new SettingsContainer($app);
        });

        $this->app->bind(TranslationStorageInterface::class, function () {
            return new DatabaseTranslationStorage();
        });

        $this->app->singleton(AdminPageNameInterface::class, function () {
            return new WezomAdminPageName();
        });

        $this->app->singleton(ButtonsContainerInterface::class, function () {
            return new ButtonsContainer();
        });

        $this->app->singleton(BreadcrumbsInterface::class, function () {
            return new Breadcrumbs();
        });

        $this->app->singleton(NavBarInterface::class, function () {
            return new NavBar();
        });

        $this->app->singleton(DashboardContainer::class, function () {
            return new DashboardContainer();
        });

        $this->app->singleton(RouteRegistrar::class, function () {
            return new RouteRegistrar(config('app.admin_prefix', 'wezom'));
        });

        $this->app->singleton(AssetManagerInterface::class, function () {
            return new WezomAssetManager();
        });

        $this->app->singleton(NotifyDriverInterface::class, function () {
            return new SwalDriver();
        });

        $this->app->bind(FilterStateStorageInterface::class, function ($app) {
            return $app[CookieStateStorage::class];
        });

        $this->app->bind(RestoreFilterInterface::class, function ($app) {
            return $app[RestoreFilter::class];
        });

        $this->app->singleton('flash-notification', function ($app) {
            return $app->make(FlashNotification::class);
        });

        $this->app->singleton('money', function () {
            return new Money();
        });

        $this->app->singleton('sidebarMenu', function ($app) {
            $app['config']->set('laravel-menu.settings.sidebarmenu.restful', true);
            $app['config']->set('laravel-menu.settings.sidebarmenu.cascade_data', false);

            return Menu::make('sidebarMenu', function (Builder $menu) {
                //
            });
        });

        $this->app->singleton('widget', function ($app) {
            return new Widget($app, $app['cache.store']);
        });

        // Extend seotools package
        $this->app->extend('seotools', function () {
            return new SEOTools();
        });

        $this->app->extend('seotools.metatags', function ($instance, $app) {
            return new SEOMeta(new ConfigRepository($app['config']->get('seotools.meta', [])));
        });

        $this->app->extend('seotools.opengraph', function ($instance, $app) {
            return new OpenGraph($app['config']->get('seotools.opengraph', []));
        });

        $this->app->extend('seotools.twitter', function ($instance, $app) {
            return new TwitterCards($app['config']->get('seotools.twitter.defaults', []));
        });

        $this->app->extend('form', function ($instance, $app) {
            $sessionStore = $app['session.store'];
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $sessionStore->token(), $app['request']);

            return $form->setSessionStore($sessionStore);
        });

        $this->app->afterResolving('form', function () {
            // runningInConsole need to cache all view templates properly (ViewCacheCommand)
            if ($this->app['isBackend'] || $this->app->runningInConsole()) {
                $this->registerFormComponents();
            }
        });

        // Extends Laravel-localization package
        $this->app->extend(\Mcamara\LaravelLocalization\LaravelLocalization::class, function ($instance, $app) {
            return new LaravelLocalization();
        });

        $this->app->extend('livewire', function () {
            return new LivewireManager();
        });

        $this->app->extend(LivewireComponentsFinder::class, function () {
            $manifestPath = config('livewire.manifest_path')
                ?: $this->app->bootstrapPath('cache/livewire-components.php');

            return new WezomLivewireComponentsFinder(
                new Filesystem(),
                $manifestPath,
                ComponentParser::generatePathFromNamespace(
                    config('livewire.class_namespace', 'App\\Http\\Livewire')
                )
            );
        });

        $this->app->singleton('locales', function () {
            $locales = [];
            foreach (\LaravelLocalization::getLocalesOrder() ?? [] as $locale => $languageRow) {
                $locales[$locale] = $languageRow['name'];
            }

            return $locales;
        });

        $this->app->singleton(PermissionsContainerInterface::class, function () {
            return new Permissions();
        });

        $this->app->bind(ExceptionHandler::class, WezomExceptionHandler::class);

        // Register Blueprint macro
        Blueprint::macro(
            'seo',
            function (array $fields = [SeoFields::TITLE, SeoFields::H1, SeoFields::KEYWORDS, SeoFields::DESCRIPTION]) {
                /** @var Blueprint $this */
                foreach ($fields as $key => $field) {
                    if (is_string($key)) {
                        $this->{$field}($key)->nullable();
                    } else {
                        $this->string($field)->nullable();
                    }
                }
            }
        );

        $this->app['config']->set('jsvalidation.disable_remote_validation', true);

        EloquentFactory::guessFactoryNamesUsing(function (string $modelName) {
            return Str::replaceFirst('\\Models\\', '\\Database\\Factories\\', $modelName) . 'Factory';
        });

        if ($this->app->runningInConsole()) {
            $this->app->extend('command.down', function () {
                return new DownCommand();
            });
        }
    }

    /**
     * Application booting.
     */
    public function boot()
    {
        $this->app['auth']->provider('activeEloquentUser', function ($app, array $config) {
            return new ActiveEloquentUserProvider($app['hash'], $config['model']);
        });

        $this->registerTranslations();

        $this->registerRouteMiddlewares();

        parent::boot();

        // runningInConsole need to cache all view templates properly (ViewCacheCommand)
        if ($this->app['isBackend'] || $this->app->runningInConsole()) {
            RegisterBladeDirectives::adminDirectives();
        }

        $this->app[AssetManagerInterface::class]
            ->addInlineScript('window.livewire_app_url = "' . rtrim(\LaravelLocalization::localizeURL('/'), '/') . '"')
            ->setSort(200)
            ->position(AssetManagerInterface::POSITION_END_BODY)
            ->group(AssetManagerInterface::GROUP_SITE);

        LifecycleManager::registerInitialHydrationMiddleware([
            'hydratePage' => function ($instance, $request) {
                if (in_array(WithPagination::class, class_uses_recursive($instance))) {
                    $instance->resolvePage();
                }
            },
        ]);
    }

    protected function afterBootForAdminPanel()
    {
        $this->registerComposers();

        /** @var NavBarInterface $navBar */
        $navBar = $this->app[NavBarInterface::class];
        $navBar->add(new Notifications());
        $navBar->add(new LanguageSwitcher());

        if (config('cms.core.main.clone_form_buttons_to_header')) {
            $navBar->add(new FormButtons());
        }
    }

    /**
     * Load module config.
     */
    protected function config()
    {
        parent::config();

        if ($this->app->configurationIsCached()) {
            return;
        }

        /** @var Config $config */
        $config = $this->app['config'];

        // Add admin guard and provider
        $config->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        $config->set('auth.providers.admins', ['driver' => 'activeEloquentUser', 'model' => Administrator::class]);
        $config->set('auth.passwords.admins', ['provider' => 'admins', 'table' => 'password_resets', 'expire' => 60]);

        // Configure api guard to admins provider
//        $config->set('auth.guards.api.provider', 'admins');

        // Increase drip interval to 30 minutes
        $config->set('genealabs-laravel-caffeine.drip-interval', 30 * 60000);

        // Override Laravel Filemanager config
        $config->set('lfm.use_package_routes', false);
        $config->set('lfm.allow_private_folder', false);
        $config->set(
            'lfm.folder_categories.file.valid_mime',
            array_merge(
                $config->get('lfm.folder_categories.file.valid_mime', []),
                ['video/webm', 'video/ogv', 'video/H264', 'video/mp4']
            )
        );
    }

    /**
     * Load module routes.
     */
    protected function routes()
    {
        \RouteRegistrar::adminRoutesWithoutAuth($this->root('routes/auth.php'));

        \RouteRegistrar::adminRoutes($this->root('routes/admin.php'));

        \RouteRegistrar::apiRoutes($this->root('routes/api.php'));

        $this->localizeLivewireRoutes();

        $this->loadRoutesFrom($this->root('routes/laravel-filemanager.php'));
    }

    protected function localizeLivewireRoutes()
    {
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->app['router']->getRoutes()->refreshActionLookups();

        $routeCollection = $this->app['router']->getRoutes();

        // Add lang prefix to livewire route.
        collect([
            $routeCollection->getByName('livewire.upload-file'),
            $routeCollection->getByName('livewire.preview-file'),
        ])->when(
            $routeCollection->hasNamedRoute('livewire.message'),
            function (Collection $routes) use ($routeCollection) {
                return $routes->push($routeCollection->getByName('livewire.message'));
            },
            function (Collection $routes) use ($routeCollection) {
                return $routes->push($routeCollection->getByAction('Livewire\Controllers\HttpConnectionHandler'));
            }
        )->filter()->map->prefix(\RouteRegistrar::getLocalizationPrefix());
    }

    protected function registerRouteMiddlewares()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];

        $router->aliasMiddleware('form_throttle', FormThrottleRequest::class);
        $router->aliasMiddleware('localize', LaravelLocalizationRoutes::class);
        $router->aliasMiddleware('localizationRedirect', LaravelLocalizationRedirectFilter::class);
    }

    /**
     * Load module views.
     */
    protected function views()
    {
        parent::views();

        RegisterBladeDirectives::globalDirectives();

        config()->set('jsvalidation.view', 'cms-core::admin.vendor.jsvalidation.bootstrap4');
        config()->set('translatable.locales', array_keys($this->app['locales']));

        $this->app['view']->share('assetManager', $this->app[AssetManagerInterface::class]);

        $this->paginationViews();
    }

    protected function paginationViews()
    {
        if ($this->app['isBackend']) {
            $paginationViews = config('cms.core.main.pagination', []);

            // Default view
            if ($defaultView = array_get($paginationViews, 'default')) {
                Paginator::defaultView($defaultView);
            }

            // Simple view
            if ($simpleView = array_get($paginationViews, 'simple')) {
                Paginator::defaultSimpleView($simpleView);
            }
        }
    }

    protected function registerFormComponents()
    {
        Form::component(
            'imageUploader',
            'cms-core::admin.components.image-uploader',
            ['name', 'obj' => null, 'deleteAction' => null, 'options' => [], 'attributes' => []]
        );

        Form::component(
            'fileUploader',
            'cms-core::admin.components.file-uploader',
            ['name', 'obj' => null, 'deleteAction' => null, 'attributes' => []]
        );

        Form::component(
            'settingsFileUploader',
            'cms-core::admin.components.settings-file-uploader',
            ['name', 'obj' => null, 'deleteAction' => null, 'attributes' => []]
        );

        Form::component(
            'slugInput',
            'cms-core::admin.components.slug-input',
            ['name', 'value', 'attributes' => []]
        );

        Form::component(
            'multipleInputs',
            'cms-core::admin.components.multiple-inputs',
            ['name', 'value', 'label', 'attributes' => []]
        );

        Form::component(
            'imageMultiUploader',
            'cms-core::admin.components.image-multi-uploader',
            ['model', 'id', 'options' => []]
        );

        Form::component(
            'map',
            'cms-core::admin.components.map.' . config('cms.core.main.map.driver'),
            [
                'name', 'value', 'multiple' => false, 'height' => config('cms.core.main.map.height'),
                'center' => config('cms.core.main.map.coordinates'), 'attributes' => []
            ]
        );

        Form::component(
            'multiLevelSelect',
            'cms-core::admin.components.multi-level-select',
            [
                'name', 'list' => [], 'selected' => null, 'disableParent' => false, 'selectAttributes' => [],
                'optionsAttributes' => []
            ]
        );
    }

    /**
     * Add directories & custom keys to translator scanner.
     */
    protected function translationScan()
    {
        parent::translationScan();

        if (is_dir(resource_path('views'))) {
            Translation::addScannerDir(resource_path('views'));
        }
    }

    protected function registerTranslations()
    {
        try {
            $translates = $this->app[TranslationStorageInterface::class]->getAllTranslations();
        } catch (Exception $e) {
            report($e);

            return;
        }

        foreach ($translates as $namespace => $locales) {
            foreach ($locales as $locale => $rows) {
                Lang::addLines($rows, $locale, $namespace);
            }
        }
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        // Super admin
        Gate::define('super_admin', function (Administrator $user) {
            return $user->isSuperAdmin();
        });

        $permissions->add('administrators', __('cms-core::admin.administrators.Administrators'), [
            'view',
            'create',
            'edit',
            'delete' => function (Administrator $administrator, Administrator $obj) {
                if ($obj->isSuperAdmin()) {
                    return false;
                }

                return $administrator->hasAccess('administrators.delete');
            }
        ]);
        $permissions->add('roles', __('cms-core::admin.roles.Roles'), [
            'view',
            'create',
            'edit',
            'delete' => function (Administrator $administrator, Role $role) {
                return !$role->isSystemRole();
            }
        ]);
        $permissions->add('translations', __('cms-core::admin.translations.Translations'), ['view', 'edit']);
        $permissions->editSettings('settings', __('cms-core::admin.settings.Edit global settings'));
    }

    protected function registerComposers()
    {
        /** @var Factory $view */
        $view = $this->app['view'];

        $view->composer('cms-core::admin.partials.footer', FooterComposer::class);
        $view->composer('cms-core::admin.partials.page-title', PageTitleComposer::class);
        $view->composer('cms-core::admin.partials.head', HeadComposer::class);
        $view->composer('cms-core::admin.partials.header', HeaderComposer::class);
        $view->composer('cms-core::admin.partials.sidebar', SidebarComposer::class);
    }

    public function adminMenu()
    {
        // Register default dashboard menu link
        SidebarMenu::add(__('cms-core::admin.layout.Dashboard'), route('admin.dashboard'))
            ->data('icon', 'fa-tachometer')
            ->data('position', -10)
            ->nickname('dashboard');

        $service = $this->serviceGroup();

        // Administration
        $administration = $service->add(__('cms-core::admin.administrators.Administrators'))
            ->data('icon', 'fa-user')
            ->data('position', 40)
            ->nickname('administrators');

        // Administrators
        $administration->add(__('cms-core::admin.administrators.Administrators'), route('admin.administrators.index'))
            ->data('permission', 'super_admin')
            ->data('icon', 'fa-user')
            ->data('position', 1)
            ->nickname('administrators');

        // Roles
        $administration->add(__('cms-core::admin.roles.Roles'), route('admin.roles.index'))
            ->data('permission', 'roles.view')
            ->data('icon', 'fa-address-card')
            ->data('position', 41)
            ->nickname('roles');

        // Translations
        $hasAdminLocales = count(config('cms.core.translations.admin.locales', [])) > 1;

        $translations = $service->add(
            __('cms-core::admin.translations.Translations'),
            $hasAdminLocales
                ? route('admin.translations', TranslationSide::ADMIN)
                : route('admin.translations', TranslationSide::SITE)
        )
            ->data('permission', 'translations.view')
            ->data('icon', 'fa-language')
            ->data('position', 43)
            ->nickname('translations');

        if ($hasAdminLocales) {
            $translations->add(
                __('cms-core::admin.translations.Admin panel'),
                route('admin.translations', TranslationSide::ADMIN)
            )
                ->data('permission', 'translations.view')
                ->data('icon', 'fa-tachometer')
                ->data('position', 0);

            $translations->add(
                __('cms-core::admin.translations.Site'),
                route('admin.translations', TranslationSide::SITE)
            )
                ->data('permission', 'translations.view')
                ->data('icon', 'fa-globe')
                ->data('position', 1);
        }

        // Settings
        SidebarMenu::add(__('cms-core::admin.settings.Global settings'), route('admin.settings.settings'))
            ->data('permission', 'settings.edit-settings')
            ->data('icon', 'fa-wrench')
            ->data('position', 100)
            ->nickname('settings');
    }

    /**
     * Register console commands.
     */
    public function registerCommands()
    {
        $this->commands([
            CreateSuperAdminCommand::class,
            InstallCommand::class,
            WidgetsCommand::class,
            ScanCommand::class,
            FindMissingCommand::class,
            MissingDefaultCommand::class,
            FreshCommand::class,
            DeleteUnusedCommand::class,
            TranslateCommand::class,
            MakeWebPCommand::class,
            DeleteLostImagesCommand::class,
            ReCropImagesCommand::class,
            ReCropSettingImagesCommand::class,
        ]);
    }
}
