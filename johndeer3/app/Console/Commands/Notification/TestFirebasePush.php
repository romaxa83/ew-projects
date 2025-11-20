<?php

namespace App\Console\Commands\Notification;

use App\Events\FcmPushGroup;
use App\Models\Notification\FcmTemplate;
use App\Repositories\Report\ReportRepository;
use App\Repositories\User\UserRepository;
use App\Services\FcmNotification\Sender\FirebaseSender;
use App\Services\FcmNotification\TemplateManager;
use Illuminate\Console\Command;

class TestFirebasePush extends Command
{
    const PUSH = 'push';
    const STATS = 'stats';
    const MESSAGE = 'message';

    protected $signature = 'noty:fcm:push';

    protected $description = 'Проверяем отправку пушей пользователя по отчету';
    /**
     * @var FirebaseSender
     */
    private $sender;
    /**
     * @var ReportRepository
     */
    private $reportRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    private $reportId;
    private $template;

    public function __construct(
        FirebaseSender $sender,
        ReportRepository $reportRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct();
        $this->sender = $sender;
        $this->reportRepository = $reportRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
//        $this->reportId = '1038';
        $this->reportId = $this->ask('Enter the reportId');

        $this->template = $this->choice(
            'Templates to choice',
            [FcmTemplate::PLANNED, FcmTemplate::POSTPONED]
        );

        $action = $this->choice(
            'What is action',
            [self::PUSH, self::STATS, self::MESSAGE]
        );

        if($action == self::PUSH){
            $this->pushNoty();
        }

        if($action === self::STATS){
            $this->stats();
        }

        if($action === self::MESSAGE){
            $this->message();
        }
    }

    private function message()
    {
        $report = $this->getReport();

        if(null === $report){
            $this->error("Not found report by id [{$this->reportId}]");
            return ;
        }
        $this->comment("message payload by report [ {$report->id} ]");

        $template = FcmTemplate::query()->where('type', $this->template)->first();

        dd((new TemplateManager($template, $report))->handle('ru'));

    }

    private function stats()
    {
        $report = $this->getReport();

        if(null === $report){
            $this->error("Not found report by id [{$this->reportId}]");
            return ;
        }
        $this->comment("stats by report [ {$report->id} ]");

        $data['admin'] = $this->userRepository->getBy('login','admin');

        $this->line("<options=underscore;bg=yellow;fg=black>---------------- ADMIN ---------------</>");
        $admin = $this->userRepository->getBy('login', 'admin');
        $this->info("[1] - admin -- login [{$admin->login}] , lang [{$admin->lang}]");
        if($admin->fcm_token){
            $this->info("\t  -- fcmToken [{$admin->fcm_token}]");
        } else {
            $this->error("\t  -- fcmToken [{$admin->fcm_token}]");
        }

        $this->line("<options=underscore;bg=yellow;fg=black>---------------- TM ---------------</>");
        foreach ($report->user->dealer->tm ?? [] as $key => $tm){
            $number = $key + 1;
            $this->info("[{$number}] - tm -- login [{$tm->login}] , lang [{$tm->lang}]");
            if($tm->fcm_token){
                $this->info("\t  -- fcmToken [{$tm->fcm_token}]");
            } else {
                $this->error("\t  -- fcmToken [{$tm->fcm_token}]");
            }
        }

        $this->line("<options=underscore;bg=yellow;fg=black>---------------- PSS ---------------</>");
        foreach ($report->reportMachines[0]->equipmentGroup->psss ?? [] as $key => $pss){
            $number = $key + 1;
            $this->info("[{$number}] - pss -- login [{$pss->login}] , lang [{$pss->lang}]");
            if($pss->fcm_token){
                $this->info("\t  -- fcmToken [{$pss->fcm_token}]");
            } else {
                $this->error("\t  -- fcmToken [{$pss->fcm_token}]");
            }
        }

        $this->line("<options=underscore;bg=yellow;fg=black>---------------- PS ---------------</>");
        $ps = $report->user;
        $this->info("[1] - ps -- login [{$ps->login}] , lang [{$ps->lang}]");
        if($ps->fcm_token){
            $this->info("\t  -- fcmToken [{$ps->fcm_token}]");
        } else {
            $this->error("\t  -- fcmToken [{$ps->fcm_token}]");
        }
    }

    // отправляем пуши
    private function pushNoty()
    {
        $report = $this->getReport();

        if(null === $report){
            $this->error("Not found report by id [{$this->reportId}]");
            return ;
        }

        $this->comment("push notification by report [ {$report->id} ]");

        try {
            event(new FcmPushGroup($report, FcmTemplate::PLANNED));

            $this->info('done');
        } catch (\Exception $error){
            $this->error($error->getMessage());
        }
    }

    private function getReport()
    {
        $report = $this->reportRepository->getByIdSomeRel($this->reportId, [
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

        return $report;
    }
}
