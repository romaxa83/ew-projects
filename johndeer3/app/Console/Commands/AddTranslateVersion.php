<?php

namespace App\Console\Commands;

use App\Models\Version;
use Illuminate\Console\Command;

/**
 * Class CreateAdmin
 *
 * @package App\Console\Commands
 */
class AddTranslateVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jd:translate:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add translate version';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        if (Version::getVersionByAlias(Version::TRANSLATES)) {
            $this->error('Translate version is exists.');
            return;
        }

        $model = new Version();
        $model->alias = Version::TRANSLATES;

        $model->save();

        $this->info('Translate version created.');
    }
}
