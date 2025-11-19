<?php

namespace Wezom\Cli\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to generate a new module
 *
 * @see ModuleMakeCommand::getArguments()
 * @see ModuleMakeCommand::getOptions()
 */
class ModuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module file structure';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ServiceProvider';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        $this->createGitIgnore();
        $this->createComposerJson();

        if ($this->option('all')) {
            $this->input->setOption('schema', true);
            $this->input->setOption('settings', true);
        }

        if ($this->option('translations')) {
            $this->createTranslations();
        }

        if ($this->option('schema')) {
            $this->createSchema();
        }

        if ($this->option('settings')) {
            $this->createSettings();
        }

        if ($this->option('structure')) {
            $this->createStructure();
        }

        return true;
    }

    protected function createGitIgnore(): void
    {
        $this->files->put(
            $this->modulePath('.gitignore'),
            file_get_contents(__DIR__ . '/stubs/module/gitignore.stub')
        );
    }

    protected function createComposerJson(): void
    {
        $content = file_get_contents(__DIR__ . '/stubs/module/composer.stub');

        $coreVersion = core_version();

        $module = parent::getNameInput();

        $replace = [
            'dummy_module' => Str::snake($module),
            'DummyNamespace' => 'Wezom\\\\' . Str::studly($module),
            'DummyServiceProvider' => Str::studly($module) . 'ServiceProvider',
            'DummyVersion' => $coreVersion,
            'DummyCoreDependencyVersion' => '^' . preg_replace('/\.\d+$/', '', $coreVersion),
        ];

        $content = str_replace(array_keys($replace), array_values($replace), $content);

        $this->files->put($this->modulePath('composer.json'), $content);
    }

    protected function createTranslations(): void
    {
        $content = file_get_contents(__DIR__ . '/stubs/module/translations.stub');

        foreach (languages() as $locale => $language) {
            $path = $this->modulePath("lang/$locale");

            $this->makeDirectory("$path/*");

            foreach ($this->option('translations') as $name) {
                $this->files->put(
                    "$path/$name.php",
                    $content
                );
            }
        }
    }

    protected function createSchema(): void
    {
        $path = $this->modulePath('graphql/schema.graphql');

        $this->makeDirectory($path);

        $this->files->put(
            $path,
            file_get_contents(__DIR__ . '/stubs/module/schema.stub')
        );
    }

    protected function createSettings(): void
    {
        $name = parent::getNameInput();

        $this->call('make:settings', [
            'name' => Str::studly($name),
            'module' => Str::snake($name),
            '--all' => true,
        ]);
    }

    protected function createStructure(): void
    {
        $directories = [
            'database/factories/',
            'database/migrations/',
            'src/Dto/',
            'src/GraphQL/Mutations/Back/',
            'src/GraphQL/Queries/Back/',
            'src/GraphQL/Queries/Site/',
            'src/ModelFilters/',
            'src/Models/',
            'src/Services/',
        ];

        foreach ($directories as $directory) {
            $this->makeDirectory($this->modulePath("$directory/*"));
        }
    }

    protected function getNameInput(): string
    {
        return Str::studly(parent::getNameInput()) . 'ServiceProvider';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $this->getNameInput());

        return $this->modulePath('src/' . str_replace('\\', '/', $name) . '.php');
    }

    protected function modulePath($path = ''): string
    {
        $module = base_path($this->option('directory')) . '/' . Str::snake(parent::getNameInput());

        return $module . ($path ? '/' . $path : $path);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/module/service-provider.stub';
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Wezom\\' . Str::studly(parent::getNameInput());
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a module with all default stuff'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the module already exists'],
            ['schema', 's', InputOption::VALUE_NONE, 'Create a GraphQL schema'],
            ['settings', 'S', InputOption::VALUE_NONE, 'Create a settings group'],
            [
                'translations',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Create translations files',
                ['permissions'],
            ],
            ['directory', 'dir', InputOption::VALUE_REQUIRED, 'Directory name with modules', 'modules'],
            ['structure', null, InputOption::VALUE_NEGATABLE, 'Create empty directories structure', true],
        ];
    }
}
