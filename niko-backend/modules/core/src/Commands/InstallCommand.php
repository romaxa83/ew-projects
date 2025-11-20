<?php

namespace WezomCms\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use WezomCms\Core\Models\Administrator;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install CMS';
    /**
     * @var Filesystem
     */
    private $files;

    /**
     * InstallCommand constructor.
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->overrideAuthenticatedMiddleware();

        $this->overrideRedirectIfAuthenticatedMiddleware();

        // Fire event
        event('cms:install', $this);

        $this->modifyRouteServiceProvider();

        $this->modifyAppBootstrapFile();

        $this->modifyCsrfTokenMiddleware();

        $this->publish();

        $this->createStorageLink();

        $this->createNotifications();

        // Fire event
        event('cms:install:before_migrate', $this);

        $this->migrate();

        // Fire event
        event('cms:install:after_migrate', $this);

        $this->call('translations:scan');

        $this->createSuperAdmin();

        $this->info('CMS successfully installed');
    }

    /**
     * Modify Authenticate Middleware.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function overrideAuthenticatedMiddleware()
    {
        $filePath = $this->laravel->basePath(config('cms.core.main.middleware_authenticate'));
        if (!$this->files->exists($filePath)) {
            $this->warn("Unable to find '{$filePath}' file!");
            return;
        }

        $fileContent = $this->files->get($filePath);

        if (str_contains($fileContent, '// Authenticated')) {
            return;
        }

        $this->files->copy($this->stub('authenticated'), $filePath);

        $this->info("Middleware '{$filePath}' overwritten!");
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function overrideRedirectIfAuthenticatedMiddleware()
    {
        $filePath = $this->laravel->basePath(config('cms.core.main.middleware_redirect_path'));
        if (!$this->files->exists($filePath)) {
            $this->warn("Unable to find '{$filePath}' file!");
            return;
        }

        $fileContent = $this->files->get($filePath);

        if (mb_strpos($fileContent, 'WezomCms\Core\Foundation\JsResponse;')) {
            return;
        }

        $this->files->copy($this->stub('redirect_if_authenticated'), $filePath);

        $this->info("Middleware '{$filePath}' overwritten!");
    }

    /**
     * Modify RouteServiceProvider class.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function modifyRouteServiceProvider()
    {
        $filePath = $this->laravel->basePath(config('cms.core.main.route_service_provider_path'));
        if (!$this->files->exists($filePath)) {
            $this->warn("Unable to find '{$filePath}' file!");

            return;
        }

        $fileContent = $this->files->get($filePath);

        if (str_contains($fileContent, 'LoadsTranslatedCachedRoutes')) {
            return;
        }

        $fileContent = preg_replace(
            '#class RouteServiceProvider extends ServiceProvider.*?\n{#',
            "class RouteServiceProvider extends ServiceProvider\n{\n    use \Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;",
            $fileContent
        );

        $this->files->put($filePath, $fileContent);

        $this->info("RouteServiceProvider '{$filePath}' updated!");
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function modifyAppBootstrapFile()
    {
        $filePath = base_path(config('cms.core.main.app_bootstrap_path'));

        $fileContent = $this->files->get($filePath);
        if (!$fileContent || str_contains($fileContent, '$app->extend(Illuminate\Foundation\PackageManifest::class')) {
            return;
        }

        $content =
            '// Extend PackageManifest class' . "\n" .
            '$app->extend(Illuminate\Foundation\PackageManifest::class, function ($instance, $app) {' . "\n" .
            '    return new \WezomCms\Core\ExtendPackage\PackageManifest(' . "\n" .
            '        new \Illuminate\Filesystem\Filesystem(), $app->basePath(), $app->getCachedPackagesPath()' . "\n" .
            '    );' . "\n" .
            '});' . "\n\n";

        $return = 'return $app;';

        $fileContent = str_replace($return, $content . $return, $fileContent);

        $this->files->put($filePath, $fileContent);

        $this->info("File 'bootstrap/app.php' updated!");
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function modifyCsrfTokenMiddleware()
    {
        $filePath = base_path(config('cms.core.main.middleware_csrf_token'));

        $fileContent = $this->files->get($filePath);
        if (!$fileContent || str_contains($fileContent, 'WezomCms\Core\Http\Middleware\VerifyCsrfToken')) {
            return;
        }

        $fileContent = str_replace(
            'use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;',
            'use WezomCms\Core\Http\Middleware\VerifyCsrfToken as Middleware;',
            $fileContent
        );

        $this->files->put($filePath, $fileContent);

        $this->info("File 'VerifyCsrfToken.php' updated!");
    }

    /**
     * Publishing all required assets.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function publish()
    {
        $publishes = [
            ['--provider' => 'WezomCms\Core\CoreServiceProvider', '--tag' => 'assets'],
            ['--provider' => 'Proengsoft\JsValidation\JsValidationServiceProvider', '--tag' => 'public'],
            ['--tag' => 'lfm_config'],
            ['--tag' => 'lfm_public'],
            ['--provider' => 'Intervention\Image\ImageServiceProviderLaravelRecent'],
        ];

        if (count(glob(database_path('migrations') . '/*_views_table.php')) === 0) {
            $publishes[] = [
                '--provider' => 'CyrildeWit\EloquentViewable\EloquentViewableServiceProvider',
                '--tag' => 'migrations'
            ];
        }

        foreach ($publishes as $arguments) {
            $this->call('vendor:publish', $arguments);
        }

        // Laravel File manager
        $this->files->copyDirectory(
            __DIR__ . '/../../publishing/laravel-filemanager',
            resource_path('lang/vendor/laravel-filemanager')
        );

        // Publish watermark and placeholders if not exists
        $watermark = pathinfo(config('cms.core.image.watermark.path'));
        foreach (
            [
                $watermark['basename'] => $watermark['dirname'],
                'no-image.png' => config('cms.core.image.placeholders.directory'),
                'no-avatar.png' => config('cms.core.image.placeholders.directory'),
            ] as $fileName => $path
        ) {
            $path = public_path($path);
            $target = "{$path}/{$fileName}";

            if (!$this->files->exists($target)) {
                if (!$this->files->isDirectory($path)) {
                    $this->files->makeDirectory($path, 0755, true);
                }

                $this->files->copy(__DIR__ . '/../../publishing/images/' . $fileName, $target);
            }
        }

        $this->modifyInterventionImageConfig();
    }

    /**
     * Create storage link.
     */
    private function createStorageLink()
    {
        $this->call('storage:link');
    }

    /**
     * Generate migrations for creating notifications table.
     */
    private function createNotifications()
    {
        if (count(glob($this->laravel->basePath('database/migrations/*_create_notifications_table.php'))) === 0) {
            $this->call('notifications:table');
        }
    }

    /**
     * Run all migrations.
     */
    private function migrate()
    {
        $this->call('migrate');
    }

    /**
     * Run console command creation super admin.
     */
    private function createSuperAdmin()
    {
        if (
            Administrator::superAdmin()->doesntExist()
            && $this->confirm('Do you want to create a super admin?', true)
        ) {
            $this->call('make:super-admin');
        }
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function modifyInterventionImageConfig()
    {
        $filePath = config_path('image.php');
        if (!$this->files->exists($filePath)) {
            $this->warn("Unable to find '{$filePath}' file!");

            return;
        }

        $fileContent = $this->files->get($filePath);

        $replacement = '\'driver\' => env(\'IMAGE_DRIVER\', \'gd\')';
        if (str_contains($fileContent, $replacement)) {
            return;
        }

        $this->files->put($filePath, str_replace('\'driver\' => \'gd\'', $replacement, $fileContent));

        $this->info("Config '{$filePath}' updated!");
    }

    /**
     * @param  string  $string
     * @return string
     */
    protected function stub(string $string): string
    {
        return __DIR__ . '/stubs/' . $string . '.stub';
    }
}
