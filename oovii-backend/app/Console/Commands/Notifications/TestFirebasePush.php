<?php

namespace App\Console\Commands\Notifications;

use Illuminate\Console\Command;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Firebase\Events\FcmGroupPush;
use WezomCms\Firebase\Events\FcmPush;
use WezomCms\Firebase\Listeners\FcmGroupPushListener;
use WezomCms\Firebase\Models\Template;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\UserRepository;

class TestFirebasePush extends Command
{
    protected $signature = 'cmd:fcm-push';

    public function __construct(protected UserRepository $userRepo)
    {
        parent::__construct();
    }

    public function handle()
    {

//        Telegram::info('📨 Send push notification', null, Telegram::LEVEL_IMPORTANT);
//dd('d');
        /** @var $user User */
        $user = $this->userRepo->getOneBy('name', 'cubic');
//
        $collection = Collection::first();
//
        event(new FcmGroupPush(Template::TYPE_COLLECTION_START, $collection));
//        event(new FcmPush($user, Template::TYPE_TEST, $collection));
    }
}

