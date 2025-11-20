<?php

namespace App\Services\Reports;

use App\Entities\Reports\ReportAdditionalEntity;
use App\Models\Departments\Department;
use App\Models\Reports\Report;
use App\Repositories\Reports\ReportRepository;
use App\Services\AbstractService;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ReportService extends AbstractService
{
    public function __construct()
    {
        $this->repo = resolve(ReportRepository::class);
        return parent::__construct();
    }

    public function createEmpty($employeeId): Report
    {
        $model = new Report();

        $model->employee_id = $employeeId;

        $model->save();

        return $model;
    }

    public function generateReportExcel(array $filters = []): string
    {
        /** @var $reports Collection */
        $reports = $this->repo->getCollection(
            [
                'items',
                'pauseItems',
                'employee.department',
                'employee.sip',
            ],
            $filters);
        /** @var $reportAdditional ReportAdditionalEntity */
        $reportAdditional = $this->repo->getAdditionalData($filters);

        $basePath = storage_path('app/public/exports/reports/');

        File::ensureDirectoryExists($basePath);

        $time = CarbonImmutable::now()->timestamp;
        $fileName = "reports-{$time}.xlsx";
        $file = $basePath . $fileName;

        $data = [];

        if(!empty($filters)){
            $tmp = [];
            unset($filters['employee_id']);
            foreach ($filters as $field => $value){
                if($field == 'department_id'){
                    $dep = DB::table(Department::TABLE)
                        ->select('name')
                        ->where('id', $value)
                        ->first();
                    $value = $dep->name;
                }
                if($field == 'employee_id'){
                    if($field == 'report_id'){
                        /** @var $report Report */
                        $report = $this->repo->getBy('id', $value);
                        $value = $report->employeeWithTrashed->getName();
                    }
                }
                $tmp[__('messages.reports.file.'.$field)] = $value;
            }

            $data[] = $tmp;
            $data[] = [];
            $data[] = [
                __('messages.reports.file.name') => __('messages.reports.file.name'),
                __('messages.reports.file.sip') => __('messages.reports.file.sip'),
                __('messages.reports.file.department') => __('messages.reports.file.department'),
                __('messages.reports.file.total_calls') => __('messages.reports.file.total_calls'),
                __('messages.reports.file.total_answered') => __('messages.reports.file.total_answered'),
                __('messages.reports.file.total_dropped') => __('messages.reports.file.total_dropped'),
                __('messages.reports.file.total_transfer') => __('messages.reports.file.total_transfer'),
                __('messages.reports.file.wait') => __('messages.reports.file.wait'),
                __('messages.reports.file.total_time') => __('messages.reports.file.total_time'),
                __('messages.reports.file.pause') => __('messages.reports.file.pause'),
                __('messages.reports.file.total_pause_time') => __('messages.reports.file.total_pause_time'),
            ];
        }

        foreach ($reports as $report) {
            /** @var $report Report */
            $data[] = [
                __('messages.reports.file.name') => valueForExcelRow($report->employeeWithTrashed->getName()),
                __('messages.reports.file.sip') => valueForExcelRow($report->employeeWithTrashed->sip?->number),
                __('messages.reports.file.department') => valueForExcelRow($report->employeeWithTrashed->department->name),
                __('messages.reports.file.total_calls') => valueForExcelRow($report->getCallsCount()),
                __('messages.reports.file.total_answered') => valueForExcelRow($report->getAnsweredCallsCount()),
                __('messages.reports.file.total_dropped') => valueForExcelRow($report->getDroppedCallsCount()),
                __('messages.reports.file.total_transfer') => valueForExcelRow($report->getTransferCallsCount()),
                __('messages.reports.file.wait') => valueForExcelRow(secondToTime($report->getTotalWait())),
                __('messages.reports.file.total_time') => valueForExcelRow(secondToTime($report->getTotalTime())),
                __('messages.reports.file.pause') => valueForExcelRow($report->getPauseCount()),
                __('messages.reports.file.total_pause_time') => valueForExcelRow(secondToTime($report->getTotalPauseTime())),
            ];
        }
        $data[] = [];
        $data[] = [
            __('messages.reports.file.name') => null,
            __('messages.reports.file.sip') => null,
            __('messages.reports.file.department') => null,
            __('messages.reports.file.total_calls') => valueForExcelRow($reportAdditional->total_calls),
            __('messages.reports.file.total_answered') => valueForExcelRow($reportAdditional->total_answer_calls),
            __('messages.reports.file.total_dropped') => valueForExcelRow($reportAdditional->total_dropped_calls),
            __('messages.reports.file.total_transfer') => valueForExcelRow($reportAdditional->total_transfer_calls),
            __('messages.reports.file.wait') => valueForExcelRow(secondToTime($reportAdditional->total_wait)),
            __('messages.reports.file.total_time') => valueForExcelRow(secondToTime($reportAdditional->total_time)),
            __('messages.reports.file.pause') => valueForExcelRow($reportAdditional->total_pause),
            __('messages.reports.file.total_pause_time') => valueForExcelRow(secondToTime($reportAdditional->total_pause_time)),
        ];

        $rowsStyle = (new StyleBuilder())
            ->setShouldWrapText()
            ->build();

        $sheets = new SheetCollection([
            'Reports' => $data
        ]);

        (new FastExcel($sheets))
            ->rowsStyle($rowsStyle)
            ->export($file)
        ;

        return url("/storage/exports/reports/{$fileName}");
    }
}
