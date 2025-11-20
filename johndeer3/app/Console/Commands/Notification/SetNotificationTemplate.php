<?php

namespace App\Console\Commands\Notification;

use App\Models\Notification\FcmTemplate;
use App\Repositories\FcmNotification\FcmNotificationRepository;
use App\Services\FcmNotification\TemplateService;
use Illuminate\Console\Command;

class SetNotificationTemplate extends Command
{
    protected $signature = 'noty:set-template';

    protected $description = 'Устанавливаем шаблоны';

    private $fcmNotificationRepository;
    private $templateService;

    public function __construct(
        FcmNotificationRepository $fcmNotificationRepository,
        TemplateService $templateService
    )
    {
        parent::__construct();
        $this->fcmNotificationRepository = $fcmNotificationRepository;
        $this->templateService = $templateService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        foreach ($this->data() as $item){
            $m = $this->fcmNotificationRepository->getOneByType($item['type']);
            if(null === $m){
                $this->templateService->create($item);
            }
        }

//        $this->info("upload done");
    }

    private function data()
    {
        return [
            [
                'type' => FcmTemplate::PLANNED,
                'vars' => ['dealer', 'date_time', 'eq_type', 'model_name', 'client', 'client_oblast', 'client_rayon'],
                'translations' => [
                    'ru' => [
                        'title' => 'Запланированная демонстрация',
                        'text' => 'Добрый день, {dealer} запланировал демонстрацию на {date_time} по категории {eq_type}, {model_name} для {client} в {client_oblast}, {client_rayon}',
                    ],
                    'en' => [
                        'title' => 'Planned demo',
                        'text' => 'Good day, {dealer} will planned on the {date_time} by category {eq_type}, {model_name} for {client} in {client_oblast}, {client_rayon}',
                    ]
                ]
            ],
            [
                'type' => FcmTemplate::POSTPONED,
                'vars' => ['dealer', 'date_time', 'previous_date_time', 'eq_type', 'model_name', 'client', 'client_oblast', 'client_rayon'],
                'translations' => [
                    'ru' => [
                        'title' => 'Отложенная демонстрация',
                        'text' => 'Добрый день, {dealer} запланировал демонстрацию на {date_time} (была {previous_date_time}) по категории {eq_type}, {model_name} для {client} в {client_oblast}, {client_rayon}',
                    ],
                    'en' => [
                        'title' => 'Postponed demo',
                        'text' => 'Good day, {dealer} will planned on the {date_time} by category {eq_type}, {model_name} for {client} in {client_oblast}, {client_rayon}',
                    ]
                ]
            ]
        ];
    }
}
