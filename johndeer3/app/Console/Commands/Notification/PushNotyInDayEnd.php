<?php

namespace App\Console\Commands\Notification;

use App\Events\FcmPushGroup;
use App\Helpers\Logger\FcmLogger;
use App\Models\Notification\FcmTemplate;
use App\Models\Report\Report;
use App\Repositories\Report\ReportRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;

class PushNotyInDayEnd extends Command
{
    protected $signature = 'jd:report:push-in-end-day';

    protected $description = 'Отправляем пуши пользователям по запланированному отчету за день в 18:00';

    private $reportRepository;

    private const HOURS = 30;

    public function __construct(ReportRepository $reportRepository)
    {
        parent::__construct();
        $this->reportRepository = $reportRepository;
    }

    public function handle()
    {
        // диапозон часов для отсылки в 18:00, это 6-30 (24ч - 18ч = 6ч)
//        $reports = $this->reportRepository->getForPushBetweenHour(6, 30, 2);

        $reports = $this->reportRepository->getPushEndDay(false, self::HOURS);

        FcmLogger::INFO("🚀🚀Кол-во отчетов для рассылки пушей at 18:00 [{$reports->count()}]");
        TelegramDev::info("🚀🚀Кол-во отчетов для рассылки пушей at 18:00 [{$reports->count()}]");

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

            $report->pushData->setSendEndDay();
        }
    }
}


