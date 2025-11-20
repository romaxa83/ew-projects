<?php

namespace WezomCms\Promotions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\TelegramBot\Services\TelegramClient;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Repositories\UserRepository;

/**
 * задача для отправки пуш уведомлений пользователям,
 * которым пришла индивидуальная акция
*/
class PromotionsPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $userIds;
    private UserRepository $userRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $userIds)
    {
        $this->userIds = $userIds;
        $this->userRepository = \App::make(UserRepository::class);

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = $this->userRepository->getUsersByIds($this->userIds);

        foreach ($users as $user){
            CallPushEvent::newPromotion($user);
        }

        Telegram::event('Пуш уведомление, отправленно ( '. count($users) .' ) пользователям, по новой акции');
    }
}
