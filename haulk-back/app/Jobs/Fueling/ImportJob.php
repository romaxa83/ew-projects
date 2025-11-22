<?php

namespace App\Jobs\Fueling;

use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Imports\FuelingImport;
use App\Models\Fueling\FuelingHistory;
use App\Services\Events\Fueling\FuelingHistoryEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private FuelingHistory $fuelingHistory;

    public function __construct(
        FuelingHistory $fuelingHistory
    ) {
        $this->fuelingHistory = $fuelingHistory;
    }

    public function handle()
    {
        $this->fuelingHistory->inProgress();
        try {
            Excel::import(
                new FuelingImport($this->fuelingHistory),
                $this->fuelingHistory->path_file,
                null,
                ExcelFormat::CSV);
            $this->fuelingHistory->ended();

        } catch (\Exception $exception) {
            logger($exception->getMessage());
            $this->fuelingHistory->failed();
        }

        FuelingHistoryEventService::fuelingHistory($this->fuelingHistory)->user($this->fuelingHistory->user)->broadcast();
    }
}
