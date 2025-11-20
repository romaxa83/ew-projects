<?php

namespace App\Console\Commands\Worker;

use App\Models\Report\Report;
use App\Services\Report\ReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldReport extends Command
{
    protected $signature = 'jd:delete-report';

    protected $description = 'Delete old report';

    protected $date;
    /**
     * @var ReportService
     */
    private $reportService;

    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        // for product
        $this->date = Carbon::now()->sub(2, 'year');
        $this->reportService = $reportService;
    }

    public function handle()
    {
        $reports = Report::query()->where('created_at', '<', $this->date)->get();

        if($reports->isEmpty()){
            \Log::notice('Old Report - нет старых отчетов для удаления');
            return;
        }

        $count = count($reports);
        $reportIds = [];

        foreach ($reports as $report){
            $this->reportService->deleteReport($report);
            array_push($reportIds, $report->id);
        }

        $strIds = implode(',', $reportIds);
        \Log::notice("Old Report - удалено ( {$count} ) старых отчета ( ids - {$strIds})");

        $this->info('Done');
    }
}
