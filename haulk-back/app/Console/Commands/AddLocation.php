<?php


namespace App\Console\Commands;



use App\Models\Locations\State;
use App\Services\ImportLocations\Worker\Import;
use Illuminate\Console\Command;

class AddLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Locations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        if (State::count() > 0) {
            $this->info('Импорт не может быть произведен');
        } else {
            try {
                $start = microtime(true);
                $import = new Import();
                $import->parse();
                $finish = microtime(true);
                $this->info($finish - $start);
                $this->info('Локации добавлены');
            } catch (\Exception $exception) {
                $this->error('Импорт не удачен');
                $this->error($exception->getMessage());
            }
        }

    }
}