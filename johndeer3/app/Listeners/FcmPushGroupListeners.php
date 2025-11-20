<?php

namespace App\Listeners;

use App\Events\FcmPushGroup;
use App\Helpers\Logger\FcmLogger;
use App\Repositories\FcmNotification\FcmNotificationRepository;
use App\Services\FcmNotification\Exception\FcmNotificationException;
use App\Services\FcmNotification\FcmNotificationService;
use App\Services\FcmNotification\FcmNotyItemPayload;
use App\Services\FcmNotification\PushDataManager;
use App\Services\FcmNotification\Sender\FirebaseSender;
use App\Services\FcmNotification\TemplateManager;
use App\Services\Telegram\TelegramDev;

class FcmPushGroupListeners
{
    public function __construct(
        protected FirebaseSender $sender,
        protected FcmNotificationRepository $repo,
        protected FcmNotificationService  $service
    ){}

    public function handle(FcmPushGroup $event)
    {
        if(config('firebase.enable_firebase')){
            try {
                $template = $this->repo
                    ->getOneByType($event->templateName, ['translations']);

                $payload = (new PushDataManager(new TemplateManager($template, $event->report)))
                    ->handleForGroup();

//                dd($payload->getItems());

                foreach ($payload->getItems() as $item){
                    /** @var $item FcmNotyItemPayload */
                    $fcm = $this->service->create($item);

                    if(null === $item->getFcmToken()){
                        $message = 'User ['. $item->getUserId() .'] not have fcm_token';
                        $fcm->setError($message);
                        FcmLogger::error($message);
//                        TelegramDev::warn($message, null, __FILE__);

                        continue;
                    }

                    $result = $this->sender->send($item, $fcm);
                    if($result){
                        $fcm->setSendStatus($result);
                        FcmLogger::info('📨📨 пуш улетел', [$item->getUserId()]);
//                        TelegramDev::info('📨📨 пуш улетел', $item->getUserId());
                    }
                }
            }
            catch (FcmNotificationException $e){
                FcmLogger::error($e->getMessage());
//                \Log::error($e->getMessage());
            }
            catch (\Throwable $e) {
                FcmLogger::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }
    }
}

