<?php

namespace App\Console\Commands\Logs;

use App\Models\Logs\Log;
use DB;
use Exception;
use Illuminate\Console\Command;
use Log as Logger;

class ClearDbLogs extends Command
{

    protected $signature = 'logs:clear';

    protected $description = 'Очистка устарелых логов в базе данных.';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        try {
            DB::beginTransaction();

            $oldest = now()
                ->subDays(config('history.logs.keep'))
                ->getTimestamp();
            Log::query()
                ->where('unix_time', '<=', $oldest)
                ->delete();

            DB::commit();

            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::rollBack();

            Logger::error($e);
        }

        return Command::FAILURE;
    }
}
