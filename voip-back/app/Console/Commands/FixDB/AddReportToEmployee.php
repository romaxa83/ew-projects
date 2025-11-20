<?php

namespace App\Console\Commands\FixDB;

use App\Models\Employees\Employee;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Reports\ReportService;
use Illuminate\Console\Command;

class AddReportToEmployee extends Command
{
    protected $signature = 'fixdb:add_report';

    protected $description = '';

    public function __construct(
        protected EmployeeRepository $employeeRepository,
        protected ReportService $reportService,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $models = $this->employeeRepository->getAll(['report']);
        foreach ($models as $model){
            /** @var $model Employee */
            if(!$model->report){
                $this->reportService->createEmpty($model->id);
            }
        }
    }
}



