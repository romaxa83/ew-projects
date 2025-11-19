<?php

namespace Wezom\Cli\Traits;

use Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

trait OperatesModulesDirectory
{
    protected function bootOperatesModulesDirectoryTrait(): void
    {
        $this->getDefinition()->addArgument(
            new InputArgument('module', InputArgument::REQUIRED, 'The module name')
        );

        $this->getDefinition()->addOption(new InputOption(
            'directory',
            'dir',
            InputOption::VALUE_REQUIRED,
            'Directory name with modules',
            'modules'
        ));
    }

    protected function modulePath($path = ''): string
    {
        $module = base_path($this->option('directory')) . '/' . Str::snake($this->argument('module'));

        return $module . ($path ? '/' . $path : $path);
    }

    protected function replaceModule(&$stub): static
    {
        $stub = str_replace(
            ['DummyModule', '{{ module }}', '{{module}}'],
            Str::studly($this->argument('module')),
            $stub
        );

        return $this;
    }
}
