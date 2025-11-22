<?php

namespace App\Console\Commands\Storage;

use Illuminate\Console\Command;
use Storage;

class CreateFileBrowserRoot extends Command
{
    protected $signature = 'storage:create-filebrowser-root';

    protected $description = 'Create base root directory for file browser.';

    public function handle(): void
    {
        $path = config('filebrowser.root');

        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);

            $this->info('Root for file browser is created!');

            return;
        }

        $this->info('Root for file browser is exists!');
    }
}
