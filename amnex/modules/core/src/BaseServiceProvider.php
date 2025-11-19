<?php

declare(strict_types=1);

namespace Wezom\Core;

use BenSampo\Enum\Enum;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\PhpEnumType;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Schema\Types\LaravelEnumType;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Wezom\Core\Contracts\Database\Seeders\ConditionalSeeder;
use Wezom\Core\Contracts\OrderColumnEnumInterface;
use Wezom\Core\Contracts\ParseGraphQlValue;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Foundation\RouteRegistrar;
use Wezom\Core\Permissions\PermissionsManager;
use Wezom\Core\Services\GraphQlTypeGeneratingService;

/**
 * @method void permissions(PermissionsManager $permissions)
 * @method void registerCommands() // Register console commands
 * @method void registerSeeders() // Register DB seeders
 * @method void registerListeners() // Register event listeners
 * @method void afterBoot() // After provider booted
 * @method void jobs(Schedule $schedule) // Add custom schedule jobs
 */
class BaseServiceProvider extends ServiceProvider
{
    private ?string $namespace = null;
    private ?string $moduleName = null;
    private ?string $root = null;
    private array $methods = [];
    protected array $seeders = [];
    protected array $morphMap = [];
    protected array $graphQlEnums = [];
    protected array $graphQlTypes = [];
    protected array $graphQlInputs = [];

    /**
     * Application booting.
     */
    public function boot(TypeRegistry $typeRegistry): void
    {
        $this->config();

        $this->translations();

        if (!($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            $this->routes();
        }

        $this->views();

        $this->callMethodIfExists('permissions');

        if ($this->morphMap) {
            $this->registerMorphMap($this->morphMap);
        }

        if ($this->app->runningInConsole()) {
            $this->callMethodIfExists('registerCommands');

            $this->migrations();

            $this->callMethodIfExists('registerSeeders');
            foreach ($this->seeders as $seeder) {
                $this->seeding($seeder);
            }

            $this->app->booted(function () {
                $this->callMethodIfExists('jobs');
            });
        }

        $this->registerGraphQlEnums($typeRegistry);

        $this->registerGraphQlTypes($typeRegistry);

        $this->registerGraphQlInputs($typeRegistry);

        $this->callMethodIfExists('registerListeners');

        $this->callMethodIfExists('afterBoot');
    }

    protected function views(): void
    {
        if (is_dir($this->root('resources/views'))) {
            $this->loadViewsFrom($this->root('resources/views'), $this->moduleName());
        }
    }

    protected function callMethodIfExists($method, array $parameters = []): void
    {
        if ($this->methodExist($method)) {
            $this->app->call([$this, $method], $parameters);
        }
    }

    protected function methodExist(string $method): bool
    {
        if (empty($this->methods)) {
            $this->methods = (array)get_class_methods(static::class);
        }

        return in_array($method, $this->methods);
    }

    /**
     * Generate module name for namespaces: view, config, translations
     */
    protected function moduleName(): string
    {
        if ($this->moduleName === null) {
            $provider = explode('\\', static::class);

            end($provider);

            $this->moduleName = snake_case(prev($provider), '-');
        }

        return $this->moduleName;
    }

    protected function config(): void
    {
        if ($this->app->configurationIsCached()) {
            return;
        }

        $repository = $this->app['config'];

        if (is_dir($this->root('config'))) {
            $files = $this->getConfigurationFiles($this->root('config'));

            foreach ($files as $key => $path) {
                $array = array_merge_recursive(config($key, []), require $path);
                $repository->set($key, $array);
            }
        }

        $this->mergeLighthouseNamespaces($repository);
    }

    /**
     * Load module translations.
     */
    protected function translations(): void
    {
        if (is_dir($this->root('lang'))) {
            $this->loadTranslationsFrom($this->root('lang'), $this->moduleName());
        }
    }

    protected function routes(): void
    {
        foreach (glob($this->root('routes/*.php')) as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            if ($fileName === 'admin') {
                $this->app[RouteRegistrar::class]->adminRoutes($file);
            } elseif ($fileName === 'site') {
                $this->app[RouteRegistrar::class]->siteRoutes($file);
            } elseif ($fileName === 'api') {
                $this->app[RouteRegistrar::class]->apiRoutes($file);
            } else {
                $this->loadRoutesFrom($file);
            }
        }
    }

    protected function migrations(): void
    {
        $directory = $this->root('database/migrations');

        if (is_dir($directory)) {
            $this->loadMigrationsFrom($directory);
        }
    }

    protected function root(?string $path = null): string
    {
        if ($this->root === null) {
            try {
                $this->root = dirname((new ReflectionClass(static::class))->getFileName(), 2);
            } catch (ReflectionException $e) {
                logger($e->getMessage(), ['class' => static::class]);
            }
        }

        return $this->root . ($path !== null ? '/' . ltrim($path, '/') : null);
    }

    protected function getConfigurationFiles($configPath): array
    {
        $files = [];

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    protected function getNestedDirectory(SplFileInfo $file, string $configPath): string
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }

    protected function namespace(): string
    {
        if ($this->namespace === null) {
            $this->namespace = (new ReflectionClass(static::class))->getNamespaceName();
        }

        return $this->namespace;
    }

    protected function seeding(string $seeder, ?callable $check = null): void
    {
        Event::listen('cms:install:after_migrate', function (Command $command) use ($seeder, $check) {
            if ($check !== null && call_user_func($check, $seeder, $command) === false) {
                $command->outputComponents()->warn('DB seeding skipped ' . $seeder);

                return;
            }

            if (is_a($seeder, ConditionalSeeder::class, true) && !app($seeder)->shouldRun()) {
                $command->outputComponents()->warn('DB seeding skipped by condition ' . $seeder);

                return;
            }

            Artisan::call('db:seed', ['--class' => $seeder, '--force' => (bool)$command->option('force')]);

            $command->outputComponents()->info('DB seeded with ' . $seeder);
        });
    }

    /**
     * @param  array<string,class-string>|array<int,class-string>  $models
     */
    private function registerMorphMap(array $models): void
    {
        $map = [];

        /** @var class-string<Model> $model */
        foreach ($models as $key => $model) {
            if (is_numeric($key)) {
                $map[(new $model())->joiningTableSegment()] = $model;
            } else {
                $map[$key] = $model;
            }
        }

        Relation::morphMap($map);
    }

    /**
     * @throws DefinitionException
     */
    private function registerGraphQlEnums(TypeRegistry $typeRegistry): void
    {
        foreach ($this->graphQlEnums as $key => $enum) {
            $alias = is_numeric($key) ? null : $key;
            if (is_subclass_of($enum, Enum::class)) {
                $type = new LaravelEnumType($enum, $alias);
            } else {
                $type = new PhpEnumType($enum, $alias);
            }

            $typeRegistry->register($type);

            if (is_subclass_of($enum, OrderColumnEnumInterface::class)) {
                $typeRegistry->register(new InputObjectType([
                    'name' => str($enum)
                        ->classBasename()
                        ->remove(['OrderColumnEnum', 'ColumnEnum'])
                        ->append('OrderInput')
                        ->value(),
                    'fields' => [
                        'column' => [
                            'type' => new NonNull($type),
                        ],
                        'direction' => [
                            'type' => new NonNull(
                                $typeRegistry->get(class_basename(OrderDirectionEnum::class))
                            ),
                        ],
                    ],
                ]));
            }
        }
    }

    private function registerGraphQlTypes(TypeRegistry $typeRegistry): void
    {
        foreach ($this->graphQlTypes as $type) {
            $typeRegistry->register(new $type());
        }
    }

    private function registerGraphQlInputs(TypeRegistry $typeRegistry): void
    {
        /** @var GraphQlTypeGeneratingService $generationService */
        $generationService = $this->app->get(GraphQlTypeGeneratingService::class);

        foreach ($this->graphQlInputs as $input) {
            $autoResolve = is_a($input, ParseGraphQlValue::class, true);

            $type = $generationService->generateInputFromDto($input, $autoResolve);
            if ($type) {
                $typeRegistry->register($type);
            }
        }
    }

    private function mergeLighthouseNamespaces(Repository $repository): void
    {
        $namespaces = array_filter([
            'models' => $this->recursiveScanNamespaces('Models'),
            'queries' => $this->recursiveScanNamespaces('GraphQL\\Queries'),
            'mutations' => $this->recursiveScanNamespaces('GraphQL\\Mutations'),
            'subscriptions' => $this->recursiveScanNamespaces('GraphQL\\Subscriptions'),
            'types' => $this->recursiveScanNamespaces('GraphQL\\Types'),
            'interfaces' => $this->recursiveScanNamespaces('GraphQL\\Interfaces'),
            'unions' => $this->recursiveScanNamespaces('GraphQL\\Unions'),
            'scalars' => $this->recursiveScanNamespaces('GraphQL\\Scalars'),
            'directives' => $this->recursiveScanNamespaces('GraphQL\\Directives'),
            'validators' => $this->recursiveScanNamespaces('GraphQL\\Validators'),
        ]);

        if (!$namespaces) {
            return;
        }

        $repository->set(
            'lighthouse.namespaces',
            array_merge_recursive(
                $repository->get('lighthouse.namespaces'),
                $namespaces
            )
        );
    }

    private function recursiveScanNamespaces(string $namespace): array
    {
        $root = $this->root('src');
        $rootDir = $root . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        if (!is_dir($rootDir)) {
            return [];
        }

        $items = [];
        foreach (Finder::create()->in($rootDir)->directories()->ignoreUnreadableDirs()->sortByName() as $dir) {
            $items[] = $dir->getPathname();
        }

        $rootNamespace = $this->namespace();

        return collect($items)
            ->map(fn (string $dir) => Str::of($dir)
                ->after($root)
                ->ltrim('/')
                ->replace(['/', '.php'], ['\\', ''])
                ->prepend($rootNamespace . '\\')
                ->value())
            ->prepend($rootNamespace . '\\' . $namespace)
            ->all();
    }
}
