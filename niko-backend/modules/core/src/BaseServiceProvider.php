<?php

namespace WezomCms\Core;

use Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Support\ServiceProvider;
use RouteRegistrar;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SitemapXmlGeneratorInterface;
use WezomCms\Core\Enums\TranslationSide;
use WezomCms\Core\Foundation\Dashboard\RegisterDashboardWidgetsTrait;
use WezomCms\Core\Foundation\Widgets\RegisterWidgetsTrait;
use WezomCms\Core\Models\Translation;

/**
 * Class BaseServiceProvider
 * @package WezomCms\Core
 * @method permissions(PermissionsContainerInterface $permissions)
 * @method adminMenu() // Register all admin sidebar menu links.
 * @method sitemapXml(SitemapXmlGeneratorInterface $sitemap)
 * @method array sitemap()
 * @method jobs(Schedule $schedule) // Add custom schedule jobs
 * @method registerCommands() // Register console commands
 */
class BaseServiceProvider extends ServiceProvider
{
    use RegisterDashboardWidgetsTrait;
    use RegisterWidgetsTrait;

    /**
     * All module widgets.
     *
     * @var array|string|null
     */
    protected $widgets;

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard;

    /**
     * Custom translation keys.
     *
     * @var array
     */
    protected $translationKeys = [];

    /**
     * List of enum classes for auto scanning localization keys.
     *
     * @var array
     */
    protected $enumClasses = [];

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];

    /**
     * @var array|null
     */
    private $methods;

    /**
     * @var string|null
     */
    private $root;

    /**
     * Current module name.
     *
     * @var string|null
     */
    private $moduleName;

    /**
     * Application booting.
     */
    public function boot()
    {
        $this->config();

        RouteRegistrar::registerMacros();

        $this->translations();

        $this->views();

        $this->routes();

        if ($this->app['isBackend']) {
            $this->callMethodIfExists('permissions');

            if (null !== $this->dashboard) {
                $this->registerDashboard($this->dashboard);
            }
        }

        if (null !== $this->widgets) {
            $this->registerWidgets($this->widgets);
        }

        // IF console
        if ($this->app->runningInConsole()) {
            $this->callMethodIfExists('registerCommands');

            $this->translationScan();

            $this->publishing();

            $this->migrations();

            $this->factories();

            $this->app->booted(function () {
                $this->callMethodIfExists('jobs');
            });
        }

        $this->registerListeners();

        $this->app['isBackend'] ? $this->afterBootForAdminPanel() : $this->afterBoot();
    }

    /**
     * Generate module name for namespaces: view, config, translations
     * @return string
     */
    protected function moduleName(): string
    {
        if (null === $this->moduleName) {
            $provider = explode('\\', static::class);

            end($provider);

            $this->moduleName = 'cms-' . snake_case(prev($provider), '-');
        }

        return $this->moduleName;
    }

    protected function afterBoot()
    {
        //
    }

    protected function afterBootForAdminPanel()
    {
        //
    }

    /**
     * Load module config.
     */
    protected function config()
    {
        if ($this->app->configurationIsCached() || !is_dir($this->root('config'))) {
            return;
        }

        $prefix = str_replace('cms-', 'cms.', $this->moduleName());

        $files = $this->getConfigurationFiles($this->root('config'));

        $repository = $this->app['config'];

        foreach ($files as $key => $path) {
            $repository->set("{$prefix}.{$key}", require $path);
        }
    }

    /**
     * Load module translations.
     */
    protected function translations()
    {
        if (is_dir($this->root('resources/lang'))) {
            $this->loadTranslationsFrom($this->root('resources/lang'), $this->moduleName());
        }
    }

    /**
     * Add directories & custom keys to translator scanner.
     */
    protected function translationScan()
    {
        Translation::addScannerDir($this->root('src'));

        if (is_dir($this->root('resources/views'))) {
            Translation::addScannerDir($this->root('resources/views'));
        }

        if ($this->translationKeys) {
            Translation::addScannerKeys($this->translationKeys);
        }

        $this->addEnumTranslationKeys();
    }

    protected function addEnumTranslationKeys()
    {
        if (empty($this->enumClasses)) {
            return;
        }

        $newKeys = [];
        foreach ($this->enumClasses as $class) {
            $parts = explode('\\', $class);

            $newKeys[snake_case(end($parts))] = $class::getValues();
        }

        if (!empty($newKeys)) {
            $moduleName = $this->moduleName();
            $allKeys = [];
            foreach (TranslationSide::getValues() as $side) {
                foreach ($newKeys as $group => $keys) {
                    foreach ($keys as $key) {
                        $allKeys[] = "{$moduleName}::{$side}.{$group}.{$key}";
                    }
                }
            }

            Translation::addScannerKeys($allKeys);
        }
    }

    /**
     * Load module views.
     */
    protected function views()
    {
        if (is_dir($this->root('resources/views'))) {
            $this->loadViewsFrom($this->root('resources/views'), $this->moduleName());
        }
    }

    /**
     * Load module routes.
     */
    protected function routes()
    {
        foreach (glob($this->root('routes/*.php')) as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);

            if ($fileName === 'admin') {
                RouteRegistrar::adminRoutes($file);
            } elseif ($fileName === 'site') {
                RouteRegistrar::siteRoutes($file);
            } elseif ($fileName === 'api') {
                RouteRegistrar::apiRoutes($file);
            } else {
                $this->loadRoutesFrom($file);
            }
        }
    }

    /**
     * Publishing.
     */
    protected function publishing()
    {
        $moduleName = $this->moduleName();
        $prefix = $prefix = str_replace('-', '/', $moduleName);

        // Configs
        if (is_dir($this->root('config'))) {
            $configs = [];
            foreach (glob($this->root('config/*.php')) as $file) {
                $path = $prefix . '/' . pathinfo($file, PATHINFO_BASENAME);

                $configs[$file] = config_path($path);
            }
            if ($configs) {
                $this->publishes($configs, 'config');
            }
        }

        // Assets
        $assetsPath = starts_with($moduleName, 'cms-') ? 'cms/' . substr($moduleName, 4) : $moduleName;
        if (is_dir($this->root('resources/assets/dist'))) {
            $this->publishes([$this->root('resources/assets/dist') => public_path('vendor/' . $assetsPath)], 'assets');
        } elseif (is_dir($this->root('resources/assets'))) {
            $this->publishes([$this->root('resources/assets') => public_path('vendor/' . $assetsPath)], 'assets');
        }

        // Migrations
        if (is_dir($this->root('database/migrations'))) {
            $this->publishes([$this->root('database/migrations') => database_path('migrations')], 'migrations');
        }

        // Views
        if (is_dir($this->root('resources/views'))) {
            $this->publishes([$this->root('resources/views') => resource_path('views/vendor/' . $moduleName)], 'views');
        }

        // Translations
        if (is_dir($this->root('resources/lang'))) {
            $this->publishes([$this->root('resources/lang') => resource_path('lang/vendor/' . $moduleName)], 'lang');
        }
    }

    /**
     * Load module migrations.
     */
    protected function migrations()
    {
        $directory = $this->root('database/migrations');

        if (is_dir($directory)) {
            $this->loadMigrationsFrom($directory);
        }
    }

    /**
     * Load module factories.
     */
    protected function factories()
    {
        $directory = $this->root('database/factories');

        if (is_dir($directory)) {
            $this->app[EloquentFactory::class]->load($directory);
        }
    }

    /**
     * Register module listeners.
     */
    protected function registerListeners()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                Event::listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            Event::subscribe($subscriber);
        }

        if ($this->app['isBackend']) {
            if ($this->methodExist('adminMenu')) {
                Event::listen('render_admin_menu', [$this, 'adminMenu']);
            }
        } else {
            if (!$this->app->runningInConsole() && $this->methodExist('sitemap')) {
                Event::listen('sitemap:site', [$this, 'sitemap']);
            }
        }

        if ($this->app->runningInConsole() && $this->methodExist('sitemapXml')) {
            Event::listen('sitemap:xml', [$this, 'sitemapXml']);
        }
    }

    /**
     * @param $method
     * @param  array  $parameters
     */
    protected function callMethodIfExists($method, array $parameters = [])
    {
        if ($this->methodExist($method)) {
            $this->app->call([$this, $method], $parameters);
        }
    }

    /**
     * @param  string  $method
     * @return bool
     */
    protected function methodExist(string $method): bool
    {
        if (null === $this->methods) {
            $this->methods = (array) get_class_methods(static::class);
        }

        return in_array($method, $this->methods);
    }

    /**
     * @param  string|null  $path
     * @return string
     */
    protected function root(string $path = null)
    {
        if (null === $this->root) {
            try {
                $this->root = dirname(dirname((new \ReflectionClass(static::class))->getFileName()));
            } catch (\ReflectionException $e) {
                logger($e->getMessage(), ['class' => static::class]);
            }
        }

        return $this->root . ($path !== null ? '/' . ltrim($path, '/') : null);
    }

    /**
     * Get all of the configuration files for the module.
     *
     * @param $configPath
     * @return array
     */
    protected function getConfigurationFiles($configPath)
    {
        $files = [];

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  \SplFileInfo  $file
     * @param  string  $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }
}
