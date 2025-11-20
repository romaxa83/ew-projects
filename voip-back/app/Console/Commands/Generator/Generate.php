<?php

namespace App\Console\Commands\Generator;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;

class Generate extends Command
{
    protected $signature = 'helpers:generate {--e=} {--f=} {--ex=}';

    protected $description = 'Создает все файлы для начальной работы сущности';

    protected string $entityName;
    protected string $entityFolder;
    protected array $exclude = [];

    protected string $className;
    protected string $stub;
    protected string $namespace;

    protected array $data = [];

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->init();

        foreach ($this->data as $item){
            $this->className = $this->entityName . data_get($item, 'after');
            $this->stub = __DIR__.'/stubs/'. data_get($item, 'stub') .'.stub';
            $this->namespace = data_get($item, 'base_namespace') . $this->entityFolder;

            $endPath = data_get($item, 'add_folder_to_path', true) ? $this->entityFolder : null;
            $path = $this->laravel['path.base'] . '/' . data_get($item, 'path') . $endPath;
            $pathFile =  $path . '/' . data_get($item, 'name', $this->className) . '.php';

            $this->makeDirectory($path);

            if(file_exists($pathFile)){
                $this->warn('Already exist - ' . $pathFile);
                continue;
            }

            $stub = $this->replaceName(
                $this->files->get($this->getStub()),
                [
                    'nameClass' => data_get($item, 'name', $this->className)
                ]
            );

            $this->files->put($pathFile, $stub);

            $this->info( 'Created - ' . $pathFile);
        }
    }

    public function init(): void
    {
        $e = $this->option('e');
        if(!$e){
            $e = $this->ask('Entity name');
        }

        $f = $this->option('f');
        if(!$f){
            $f = $this->ask('Entity folder');
        }

        $this->entityName = ucfirst($e);
        $this->entityFolder = ucfirst($f);

        // какие файлы не создавать
        $ex = $this->option('ex');
        if($ex){
            $this->exclude = explode(',', $ex);
        }

        $this->data = $this->datum();
        foreach ($this->exclude as $key){
            if($key === 't'){
                unset(
                    $this->data['t-f'],
                    $this->data['t-mi'],
                    $this->data['t-d'],
                );
            }
            if($key === 'p'){
                unset(
                    $this->data['p-c'],
                    $this->data['p-u'],
                    $this->data['p-d'],
                    $this->data['p-l'],
                );
            }
            unset($this->data[$key]);
        }
    }

    protected function makeDirectory($path)
    {
        $this->files->makeDirectory($path, 0777, true, true);

        return $path;
    }

    protected function replaceName(string $stub, array $additional = []): string
    {
        $dbName = lcfirst($this->entityFolder);
        $dbNameTranslation = lcfirst($this->entityName) . '_translations';
        $nameTranslation = lcfirst($this->entityName) . 'Translation';

        return str_replace(
            [
                'DummyNamespace',
                'DummyClass',
                'DummyFolder',
                'DummyEntity',
                'DummyDBName',
                'DummyDBNameTranslation',
                'DummyEntityTranslation',
            ],
            [
                $this->namespace,
                data_get($additional, 'nameClass'),
                $this->entityFolder,
                $this->entityName,
                $dbName,
                $dbNameTranslation,
                $nameTranslation
            ],
            $stub
        );
    }

    protected function getStub()
    {
        return $this->stub;
    }

    public function datum(): array
    {
        return [
            // модель
            'm' => [
                'stub' => 'model',
                'base_namespace' => 'App\Models\\',
                'path' => 'app/Models/',
            ],
            // фабрика для модели
            'm-f' => [
                'stub' => 'model_factory',
                'base_namespace' => 'Database\Factories\\',
                'after' => 'Factory',
                'path' => 'database/factories/',
            ],
            // переводы для модели
            't' => [
                'stub' => 'model_translation',
                'base_namespace' => 'App\Models\\',
                'after' => 'Translation',
                'path' => 'app/Models/',
            ],
            // переводы для модели
            't-f' => [
                'stub' => 'model_translation_factory',
                'base_namespace' => 'Database\Factories\\',
                'after' => 'TranslationFactory',
                'path' => 'database/factories/',
            ],
            // репозиторий
            'r' => [
                'stub' => 'repository',
                'base_namespace' => 'App\Repositories\\',
                'after' => 'Repository',
                'path' => 'app/Repositories/',
            ],
            // сервис
            's' => [
                'stub' => 'service',
                'base_namespace' => 'App\Services\\',
                'after' => 'Service',
                'path' => 'app/Services/',
            ],
            // миграция для модели
            'mi' => [
                'stub' => 'migration',
                'path' => 'database/migrations',
                'add_folder_to_path' => false,
                'name' => Carbon::now()->format('Y_m_d') .'_000001_create_'. lcfirst($this->entityFolder) . '_table',
            ],
            // миграция для модели переводов
            't-mi' => [
                'stub' => 'migration_translation',
                'path' => 'database/migrations',
                'add_folder_to_path' => false,
                'name' => Carbon::now()->format('Y_m_d') .'_000002_create_'. lcfirst($this->entityName) . '_translations_table',
            ],
            'd' => [
                'stub' => 'dto',
                'base_namespace' => 'App\Dto\\',
                'after' => 'Dto',
                'path' => 'app/Dto/',
            ],
            't-d' => [
                'stub' => 'dto_translation',
                'base_namespace' => 'App\Dto\\',
                'after' => 'TranslationDto',
                'path' => 'app/Dto/',
            ],
            // пермишены
            'p' => [
                'stub' => 'permission',
                'base_namespace' => 'App\Permissions\\',
                'path' => 'app/Permissions/',
                'name' => 'PermissionGroup',
            ],
            'p-c' => [
                'stub' => 'permission_create',
                'base_namespace' => 'App\Permissions\\',
                'path' => 'app/Permissions/',
                'name' => 'CreatePermission',
            ],
            'p-u' => [
                'stub' => 'permission_update',
                'base_namespace' => 'App\Permissions\\',
                'path' => 'app/Permissions/',
                'name' => 'UpdatePermission',
            ],
            'p-d' => [
                'stub' => 'permission_delete',
                'base_namespace' => 'App\Permissions\\',
                'path' => 'app/Permissions/',
                'name' => 'DeletePermission',
            ],
            'p-l' => [
                'stub' => 'permission_list',
                'base_namespace' => 'App\Permissions\\',
                'path' => 'app/Permissions/',
                'name' => 'listPermission',
            ],
        ];
    }
}
