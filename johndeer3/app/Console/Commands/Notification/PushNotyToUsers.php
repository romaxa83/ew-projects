<?php

namespace App\Console\Commands\Notification;

use App\Events\FcmPushGroup;
use App\Helpers\Logger\FcmLogger;
use App\Models\Notification\FcmTemplate;
use App\Models\Report\Report;
use App\Repositories\Report\ReportRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;

class PushNotyToUsers extends Command
{
    protected $signature = 'jd:report:push';

    protected $description = 'Отправляем пуши пользователям по запланированному отчету';
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
        $reports = $this->reportRepository->getPushForWeek(false);
//dd($reports);
        TelegramDev::info("🚀🚀Кол-во отчетов для рассылки пушей за неделю [{$reports->count()}]");
        FcmLogger::INFO("🚀🚀Кол-во отчетов для рассылки пушей за неделю [{$reports->count()}]");

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

            TelegramDev::info("🏁 Запущен процесс рассылки пушей для отчета [{$report->id}], с шаблоном [{$template}]");
            FcmLogger::INFO("🏁 Запущен процесс рассылки пушей для отчета [{$report->id}], с шаблоном [{$template}]");
            event(new FcmPushGroup($report, $template));

            $report->pushData->setSendWeek();
        }
    }
}
