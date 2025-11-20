<?php

namespace App\Console\Commands\Notification;

use App\Models\Report\Report;
use App\Repositories\Report\ReportRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class ReportForPush extends Command
{
    protected $signature = 'noty:report:push-list';

    protected $description = 'Выводим данные по отчетам';
    /**
     * @var ReportRepository
     */
    private $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        parent::__construct();
        $this->reportRepository = $reportRepository;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->sendWeek();
        $this->sendStart();
        $this->sendEnd();
    }

    private function sendWeek()
    {
        $now = Carbon::today()->addHours(10);
        $reports = $this->reportRepository->getPushForWeek(false, 7, $now);

        $this->registerCustomTableStyle();
        $headers = [
            [new TableCell("Список отчетов, подходящих для рассылки пушей за неделю  -  [{$now}]", ['colspan' => 6])],
            ['id', 'title', 'planned_at', 'diff (hour)', 'is_send_week', 'prev_planned_at',]
        ];

        $data = $reports->map(function (Report $report) use($now) {

            $diff = $report->pushData->planned_at->diffInHours($now);
            return [
                'id' => $report->id,
                'name' => $report->title,
                'planned_at' => $report->pushData->planned_at,
                'diff' => $diff,
                'is_send_week' => $report->pushData->is_send_week ? 'true' : 'false',
                'prev_planned_at' => $report->pushData->prev_planned_at,
            ];
        });

        $percentage = (count($reports) / $this->reportRepository->count()) * 100;

        $data->push(new TableSeparator());
        $data->push([new TableCell(
            sprintf('%f%% готовых к отправке по отношению ко всем отчетам', $percentage),
            ['colspan' => 4]
        )]);

        $this->table($headers, $data, 'secrets');
    }

    private function sendStart()
    {
        $now = Carbon::today()->addHours(9);
        $reports = $this->reportRepository->getPushStartDay(false, 39, $now);

        $this->registerCustomTableStyle();
        $headers = [
            [new TableCell("Список отчетов, подходящих для рассылки в 9.00  -  [{$now}]", ['colspan' => 6])],
            ['id', 'title', 'planned_at', 'diff (hour)', 'is_send_start_day', 'prev_planned_at',]
        ];

        $data = $reports->map(function (Report $report) use($now) {

            $diff = $report->pushData->planned_at->diffInHours($now);
            return [
                'id' => $report->id,
                'name' => $report->title,
                'planned_at' => $report->pushData->planned_at,
                'diff' => $diff,
                'is_send_start_day' => $report->pushData->is_send_start_day ? 'true' : 'false',
                'prev_planned_at' => $report->pushData->prev_planned_at,
            ];
        });

        $percentage = (count($reports) / $this->reportRepository->count()) * 100;

        $data->push(new TableSeparator());
        $data->push([new TableCell(
            sprintf('%f%% готовых к отправке по отношению ко всем отчетам', $percentage),
            ['colspan' => 4]
        )]);

        $this->table($headers, $data, 'secrets');
    }

    private function sendEnd()
    {
        $now = Carbon::today()->addHours(18);
        $reports = $this->reportRepository->getPushEndDay(false, 30, $now);

        $this->registerCustomTableStyle();
        $headers = [
            [new TableCell("Список отчетов, подходящих для рассылки в 18.00  -  [{$now}]", ['colspan' => 6])],
            ['id', 'title', 'planned_at', 'diff (hour)', 'is_send_end_day', 'prev_planned_at',]
        ];

        $data = $reports->map(function (Report $report) use($now) {
            $diff = $report->pushData->planned_at->diffInHours($now);
            return [
                'id' => $report->id,
                'name' => $report->title,
                'planned_at' => $report->pushData->planned_at,
                'diff' => $diff,
                'is_send_end_day' => $report->pushData->is_send_end_day ? 'true' : 'false',
                'prev_planned_at' => $report->pushData->prev_planned_at,
            ];
        });

        $percentage = (count($reports) / $this->reportRepository->count()) * 100;

        $data->push(new TableSeparator());
        $data->push([new TableCell(
            sprintf('%f%% готовых к отправке по отношению ко всем отчетам', $percentage),
            ['colspan' => 4]
        )]);

        $this->table($headers, $data, 'secrets');
    }

//    private function sendWeek()
//    {
//        $reports = $this->reportRepository->getPushForWeek();
//        $now = Carbon::now();
//
//
//        $this->registerCustomTableStyle();
//        $headers = [
//            [new TableCell("Список отчетов, подходящих для рассылки пушей  -  [{$now}]", ['colspan' => 6])],
//            ['id', 'title', 'planned_at', 'diff (hour)', 'send_push', 'prev_planned_at', 'in 9:00', 'in 18:00']
//        ];
//
//        $data = $reports->map(function (Report $report) use($now) {
//
//            $diff = $report->pushData->planned_at->diffInHours($now);
//
////            $diffToday = $report->pushData->planned_at->diffInHours(Carbon::today());
//
//            $beginDay = '9';
//            $endDay = '18';
//            $twoDays = '48';
//
//            return [
//                'id' => $report->id,
//                'name' => $report->title,
//                'planned_at' => $report->pushData->planned_at,
//                'diff' => $diff,
//                'send_push' => $report->pushData->send_push ? 'true' : 'false',
//                'prev_planned_at' => $report->pushData->prev_planned_at,
//                'in 9:00' => ($diff > $beginDay && $diff < ($twoDays - $beginDay)) ? 'true' : 'false',
//                'in 18:00' => ($diff > $endDay && $diff < ($twoDays - $endDay)) ? 'true' : 'false',
//            ];
//        });
//
//        $allCount = $this->reportRepository->count();
//        $percentage = (count($reports) / $this->reportRepository->count()) * 100;
//
//        $data->push(new TableSeparator());
//        $data->push([new TableCell(
//            sprintf('%f%% готовых к отправке по отношению ко всем отчетам', $percentage),
//            ['colspan' => 4]
//        )]);
//
//        $this->table($headers, $data, 'secrets');
//    }

    private function registerCustomTableStyle()
    {
        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>')
        ;
        Table::setStyleDefinition('secrets', $tableStyle);
    }
}
