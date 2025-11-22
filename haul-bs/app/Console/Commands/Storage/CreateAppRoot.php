<?php

namespace App\Console\Commands\Storage;

use Illuminate\Console\Command;
use Storage;

class CreateAppRoot extends Command
{
    protected $signature = 'storage:create-app-root';

    protected $description = 'Create base root directory for application.';

    public function handle(): void
    {
        $path = config('media-library.directory');

        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
            $this->info('Root directory for app is created!');

            return;
        }

        $this->info('Root directory for app is exists!');
    }
}
