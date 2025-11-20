<?php

namespace App\Services\Reports;

use App\Entities\Reports\ReportItemAdditionalEntity;
use App\Entities\Reports\ReportPauseItemAdditionalEntity;
use App\Enums\Formats\DatetimeEnum;
use App\Models\Reports\Item;
use App\Models\Reports\PauseItem;
use App\Models\Reports\Report;
use App\Repositories\Reports\ReportPauseItemRepository;
use App\Repositories\Reports\ReportRepository;
use App\Services\AbstractService;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ReportPauseItemService extends AbstractService
{
    public function __construct(
        protected ReportRepository $reportRepository,
    )
    {
        $this->repo = resolve(ReportPauseItemRepository::class);
        return parent::__construct();
    }

    public function insert(array $data): void
    {
        $recs = [];

        foreach ($data as $item){
            $recs[] = [
                'report_id' => $item['report_id'],
                'pause_at' => CarbonImmutable::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $item['pause_data']['time']),
                'unpause_at' => CarbonImmutable::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $item['unpause_data']['time']),
                'data' => arrayToJson($item),
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
            ];
        }

        PauseItem::query()->insert($recs);
    }

    public function generateExcel(array $filters = []): string
    {
        /** @var $items Collection */
        $items = $this->repo->getCollection(
            [],
            $filters);

        /** @var $reportPauseItemAdditional ReportPauseItemAdditionalEntity */
        $reportPauseItemAdditional = $this->repo->getAdditionalData($filters);

        $basePath = storage_path('app/public/exports/reports/');

        File::ensureDirectoryExists($basePath);

        $time = CarbonImmutable::now()->timestamp;
        $fileName = "report-pause-items-{$time}.xlsx";
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
                __('messages.reports.file.pause_at') => __('messages.reports.file.pause_at'),
                __('messages.reports.file.unpause_at') => __('messages.reports.file.unpause_at'),
                __('messages.reports.file.duration') => __('messages.reports.file.duration'),
                __('messages.reports.file.pause') => __('messages.reports.file.pause'),
                __('messages.reports.file.total_pause_time') => __('messages.reports.file.total_pause_time'),
            ];
        }

        foreach ($items as $k => $item) {
            /** @var $item PauseItem */
            $data[] = [
                __('messages.reports.file.pause_at') => dateByTz($item->pause_at)->format(DatetimeEnum::FOR_EXCEL_FILE),
                __('messages.reports.file.unpause_at') => dateByTz($item->unpause_at)->format(DatetimeEnum::FOR_EXCEL_FILE),
                __('messages.reports.file.duration') => valueForExcelRow(secondToTime($item->getDiffAtBySec())),
                __('messages.reports.file.pause') => null,
                __('messages.reports.file.total_pause_time') => null,
            ];
        }
        $data[] = [];
        if($items->isNotEmpty()){
            $data[] = [
                __('messages.reports.file.pause_at') => null,
                __('messages.reports.file.unpause_at') => null,
                __('messages.reports.file.duration') => null,
                __('messages.reports.file.pause') => valueForExcelRow($reportPauseItemAdditional->pause),
                __('messages.reports.file.total_pause_time') => valueForExcelRow(secondToTime($reportPauseItemAdditional->total_pause_time)),
            ];
        }

        $rowsStyle = (new StyleBuilder())
            ->setShouldWrapText()
            ->build();

        $sheets = new SheetCollection([
            'Report-pause-items' => $data
        ]);

        (new FastExcel($sheets))
            ->rowsStyle($rowsStyle)
            ->export($file)
        ;

        return url("/storage/exports/reports/{$fileName}");
    }
}
