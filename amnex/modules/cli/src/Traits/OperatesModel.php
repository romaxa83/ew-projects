<?php

namespace Wezom\Cli\Traits;

use Str;
use Symfony\Component\Console\Input\InputOption;

trait OperatesModel
{
    use OperatesModulesDirectory;

    protected function bootOperatesModelTrait(): void
    {
        $this->bootOperatesModulesDirectoryTrait();

        $this->getDefinition()->addOption(new InputOption(
            'model',
            'M',
            InputOption::VALUE_OPTIONAL,
            'The name of the model'
        ));
    }

    /**
     * Guess the model name from the Factory name or return a default model name.
     */
    protected function guessModelName(string $name): string
    {
        if (str_ends_with($name, $this->type)) {
            $name = substr($name, 0, -strlen($this->type));
        }

        $modelName = $this->manageModel(Str::after($name, $this->modelNamespace()));

        if (class_exists($modelName)) {
            return $modelName;
        }

        return $this->modelNamespace() . 'Model';
    }

    /**
     * Get the root namespace for the class.
     */
    protected function modelNamespace(): string
    {
        return 'Wezom\\' . Str::studly($this->argument('module')) . '\\Models\\';
    }

    protected function manageModel(string $model): string
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $namespace = $this->modelNamespace();

        if (Str::startsWith($model, $namespace)) {
            return $model;
        }

        return $namespace . $model;
    }

    protected function replaceModel(&$stub, string $name): static
    {
        $namespaceModel = $this->option('model')
            ? $this->manageModel($this->option('model'))
            : $this->manageModel($this->guessModelName($name));

        $model = class_basename($namespaceModel);

        $stub = str_replace(
            ['DummyModel', '{{ model }}', '{{model}}'],
            $model,
            $stub
        );

        return $this;
    }

    protected function replaceNamespacedModel(&$stub, string $name): static
    {
        $namespaceModel = $this->option('model')
            ? $this->manageModel($this->option('model'))
            : $this->manageModel($this->guessModelName($name));

        $stub = str_replace(
            ['NamespacedDummyModel', '{{ namespacedModel }}', '{{namespacedModel}}'],
            $namespaceModel,
            $stub
        );

        return $this;
    }
}
