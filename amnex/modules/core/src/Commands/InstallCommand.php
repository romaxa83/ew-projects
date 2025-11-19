<?php

namespace Wezom\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'cms:install {--force : Force the operation to run when in production}';

    public function handle(Filesystem $files): int
    {
        event('cms:install:before_migrate', $this);

        $this->call('migrate', ['--force' => (bool)$this->option('force')]);

        event('cms:install:after_migrate', $this);

        if (!$files->exists(public_path('storage'))) {
            $this->call('storage:link');
        }

        return self::SUCCESS;
    }
}
