<?php

namespace App\Console\Commands\Notification;

use App\Events\FcmPushGroup;
use App\Helpers\Logger\FcmLogger;
use App\Models\Notification\FcmTemplate;
use App\Models\Report\Report;
use App\Repositories\Report\ReportRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;

class PushNotyInDayStart extends Command
{
    protected $signature = 'jd:report:push-in-start-day';

    protected $description = 'Отправляем пуши пользователям по запланированному отчету за день в 9:00';

    private $reportRepository;

    private const HOURS = 39;

    public function __construct(ReportRepository $reportRepository)
    {
        parent::__construct();
        $this->reportRepository = $reportRepository;
    }

    public function handle()
    {
        // диапозон часов для отсылки в 9:00, это 15-39 (24ч - 9ч = 15ч)
//        $reports = $this->reportRepository->getForPushBetweenHour(15, 39, 2);

        $reports = $this->reportRepository->getPushStartDay(false, self::HOURS);

        TelegramDev::info("🚀🚀Кол-во отчетов для рассылки пушей at 9:00 [{$reports->count()}]");
        FcmLogger::INFO("🚀🚀Кол-во отчетов для рассылки пушей at 9:00 [{$reports->count()}]");

        foreach ($reports as $report){
            /** @var $report Report */
            $report->load([
                'user',
                'user.profile',
                'user.dealer',
                'user.dealer.tm',
                'clients',
                'clients.region',
                'reportClients',
                'location',
                'pushData',
                'reportMachines',
                'reportMachines.equipmentGroup.psss',
                'reportMachines.modelDescription',
            ]);

            $template = $report->pushData->prev_planned_at ? FcmTemplate::POSTPONED : FcmTemplate::PLANNED;

            TelegramDev::info("🏁 Запущен процесс по рассылки пушей для отчета [{$report->id}], с шаблоном [{$template}]");
            FcmLogger::INFO("🏁 Запущен процесс по рассылки пушей для отчета [{$report->id}], с шаблоном [{$template}]");
            event(new FcmPushGroup($report, $template));

            $report->pushData->setSendStartDay();
        }
    }
}

