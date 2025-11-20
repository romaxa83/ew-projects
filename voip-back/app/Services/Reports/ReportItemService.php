<?php

namespace App\Services\Reports;

use App\Dto\Reports\ReportItemDto;
use App\Entities\Reports\ReportItemAdditionalEntity;
use App\Entities\Reports\ReportPauseItemAdditionalEntity;
use App\Enums\Formats\DatetimeEnum;
use App\Models\Calls\History;
use App\Models\Employees\Employee;
use App\Models\Reports\Item;
use App\Models\Reports\Report;
use App\Repositories\Calls\HistoryRepository;
use App\Repositories\Reports\ReportItemRepository;
use App\Repositories\Reports\ReportPauseItemRepository;
use App\Repositories\Reports\ReportRepository;
use App\Services\AbstractService;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ReportItemService extends AbstractService
{
    public function __construct(
        protected HistoryRepository $historyRepository,
        protected ReportRepository $reportRepository,
        protected ReportPauseItemRepository $reportPauseItemRepository
    )
    {
        $this->repo = resolve(ReportItemRepository::class);
        return parent::__construct();
    }

    public function create(ReportItemDto $dto): Item
    {
        if(
            !$dto->name &&
            $history = DB::table(History::TABLE)
                ->where('from_num', $dto->num)
                ->whereNotNull('from_name_pretty')
                ->first()
        ){
            $dto->name = $history->from_name_pretty;
        }

        $model = new Item();

        $model->report_id = $dto->reportID;
        $model->callid = $dto->callID;
        $model->name = $dto->name;
        $model->num = $dto->num;
        $model->status = $dto->status;
        $model->wait = $dto->wait;
        $model->total_time = $dto->totalTime;
        $model->call_at = $dto->callAt;

        $model->save();

        return $model;
    }

    public function generateExcel(array $filters = []): string
    {
        /** @var $items Collection */
        $items = $this->repo->getCollection(
            [],
            $filters);

        /** @var $reportItemAdditional ReportItemAdditionalEntity */
        $reportItemAdditional = $this->repo->getAdditionalData($filters);
        /** @var $reportPauseItemAdditional ReportPauseItemAdditionalEntity */
        $reportPauseItemAdditional = $this->reportPauseItemRepository->getAdditionalData($filters);

        $basePath = storage_path('app/public/exports/reports/');

        File::ensureDirectoryExists($basePath);

        $time = CarbonImmutable::now()->timestamp;
        $fileName = "report-items-{$time}.xlsx";
        $file = $basePath . $fileName;

        $data = [];

        if(!empty($filters)){
            $tmp = [];
            unset($filters['employee_id']);
            foreach ($filters as $field => $value){
                if($field == 'report_id'){
                    /** @var $report Report */
                    $report = $this->reportRepository->getBy('id', $value);
                    $value = $report->employeeWithTrashed->getName();
                }
                $tmp[__('messages.reports.file.'.$field)] = $value;
            }

            $data[] = $tmp;
            $data[] = [];
            $data[] = [
                __('messages.reports.file.date') => __('messages.reports.file.date'),
                __('messages.reports.file.number') => __('messages.reports.file.number'),
                __('messages.reports.file.name') => __('messages.reports.file.name'),
                __('messages.reports.file.wait') => __('messages.reports.file.wait'),
                __('messages.reports.file.total_time') => __('messages.reports.file.total_time'),
                __('messages.reports.file.status') => __('messages.reports.file.status'),
                ' ' => null,
                __('messages.reports.file.total_dropped') => __('messages.reports.file.total_dropped'),
                __('messages.reports.file.total_calls') => __('messages.reports.file.total_calls'),
                __('messages.reports.file.pause') => __('messages.reports.file.pause'),
                __('messages.reports.file.total_pause_time') => __('messages.reports.file.total_pause_time'),
            ];
        }

        foreach ($items as $k => $item) {
            /** @var $item Item */
            $data[] = [
                __('messages.reports.file.date') => dateByTz($item->call_at)->format(DatetimeEnum::FOR_EXCEL_FILE),
                __('messages.reports.file.number') => valueForExcelRow($item->num),
                __('messages.reports.file.name') => valueForExcelRow($item->name),
                __('messages.reports.file.wait') => valueForExcelRow(secondToTime($item->wait)),
                __('messages.reports.file.total_time') => valueForExcelRow(secondToTime($item->total_time)),
                __('messages.reports.file.status') => $item->status->prettyValue(),
                    ' ' => null,
                __('messages.reports.file.total_dropped') => $k == 0
                    ? valueForExcelRow($reportItemAdditional->total_dropped)
                    : null,
                __('messages.reports.file.total_calls') => $k == 0
                    ? valueForExcelRow($reportItemAdditional->total_calls)
                    : null,
                __('messages.reports.file.pause') => $k == 0
                    ? valueForExcelRow($reportPauseItemAdditional->pause)
                    : null,
                __('messages.reports.file.total_pause_time') => $k == 0
                    ? valueForExcelRow(secondToTime($reportPauseItemAdditional->total_pause_time))
                    : null,
            ];
        }
        $data[] = [];
        if($items->isNotEmpty()){
            $data[] = [
                __('messages.reports.file.date') => null,
                __('messages.reports.file.number') => null,
                __('messages.reports.file.name') => null,
                __('messages.reports.file.wait') => valueForExcelRow(secondToTime($reportItemAdditional->total_wait)),
                __('messages.reports.file.total_time') => valueForExcelRow(secondToTime($reportItemAdditional->total_time)),
                __('messages.reports.file.status') => null,
                ' ' => null,
                __('messages.reports.file.total_dropped') => null,
                __('messages.reports.file.total_calls') => null,
                __('messages.reports.file.pause') => null,
                __('messages.reports.file.total_pause_time') => null,
            ];
        } elseif (
            $items->isEmpty() && $reportPauseItemAdditional->pause
        ) {
            $data[] = [
                __('messages.reports.file.date') => null,
                __('messages.reports.file.number') => null,
                __('messages.reports.file.name') => null,
                __('messages.reports.file.wait') => null,
                __('messages.reports.file.total_time') => null,
                __('messages.reports.file.status') => null,
                ' ' => null,
                __('messages.reports.file.total_dropped') => null,
                __('messages.reports.file.total_calls') => null,
                __('messages.reports.file.pause') => valueForExcelRow($reportPauseItemAdditional->pause),
                __('messages.reports.file.total_pause_time') => valueForExcelRow(secondToTime($reportPauseItemAdditional->total_pause_time)),
            ];
        }

        $rowsStyle = (new StyleBuilder())
            ->setShouldWrapText()
            ->build();

        $sheets = new SheetCollection([
            'Report-items' => $data
        ]);

        (new FastExcel($sheets))
            ->rowsStyle($rowsStyle)
            ->export($file)
        ;

        return url("/storage/exports/reports/{$fileName}");
    }
}
