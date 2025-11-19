<?php

namespace Wezom\Cli\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Wezom\Cli\Traits\OperatesModel;

/**
 * Make a CMS model filter command
 *
 * @see FactoryMakeCommand::getOptions()
 */
#[AsCommand('make:cms-factory', 'Create a new model factory')]
class FactoryMakeCommand extends GeneratorCommand
{
    use OperatesModel;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Factory';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->bootOperatesModelTrait();
    }

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

        if ($this->option('multilingual')) {
            $this->createTranslation();
        }

        return true;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $class = Str::ucfirst(str_replace('Factory', '', $name));

        $stub = parent::buildClass($class);

        $name = class_basename($class);

        $this->replaceNamespacedModel($stub, $name)
            ->replaceModel($stub, $name);

        return $stub;
    }

    protected function createTranslation(): void
    {
        $name = $this->qualifyClass($this->getNameInput());

        $class = Str::ucfirst(str_replace('Factory', '', $name));

        $path = $this->getPath($class . 'Translation');

        $name = class_basename($class);

        if ($this->files->exists($path)) {
            $this->components->error($path . ' already exists.');

            return;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/model/factory-translation.stub');
        $this->files->put(
            $path,
            $this->replaceNamespace($stub, $class)
                ->replaceNamespacedModel($stub, $name)
                ->replaceModel($stub, $name)
                ->replaceClass($stub, $class)
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'TranslationFactory', $path));
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub(): string
    {
        return $this->option('multilingual')
            ? __DIR__ . '/stubs/model/factory.translated.stub'
            : __DIR__ . '/stubs/model/factory.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $name = (string)Str::of($name)->replaceFirst($this->rootNamespace(), '')->finish('Factory');

        return $this->modulePath('database/factories/' . str_replace('\\', '/', $name) . '.php');
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Wezom\\' . Str::studly($this->argument('module')) . '\\Database\\Factories\\';
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Allow to create multilingual factory when basic factory exists.'],
            ['multilingual', 'm', InputOption::VALUE_NONE, 'Model should be multilingual'],
        ];
    }
}
