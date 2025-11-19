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
 * @see FilterMakeCommand::getOptions()
 */
#[AsCommand('make:filter', 'Create a new model filter')]
class FilterMakeCommand extends GeneratorCommand
{
    use OperatesModel;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ModelFilter';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->bootOperatesModelTrait();
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
        $class = (string)Str::of($name)->replaceLast('Filter', '')->ucfirst();

        $stub = parent::buildClass($class);

        $name = class_basename($class);

        $this->replaceNamespacedModel($stub, $name)
            ->replaceModel($stub, $name);

        return $stub;
    }

    protected function replaceTraits(string &$stub, array $traits): static
    {
        $stub = str_replace(
            '{{traits}}',
            implode(array_map(fn (string $class) => "    use \\$class;\n", $traits)),
            $stub
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub(): string
    {
        return $this->option('multilingual')
            ? __DIR__ . '/stubs/model/filter.translated.stub'
            : __DIR__ . '/stubs/model/filter.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $name = (string)Str::of($name)->replaceFirst($this->rootNamespace(), '')->finish('Filter');

        return $this->modulePath('src/ModelFilters/' . str_replace('\\', '/', $name) . '.php');
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Wezom\\' . Str::studly($this->argument('module')) . '\\ModelFilters\\';
    }

    protected function getOptions(): array
    {
        return [
            ['multilingual', 'm', InputOption::VALUE_NONE, 'Model should be multilingual'],
        ];
    }
}
