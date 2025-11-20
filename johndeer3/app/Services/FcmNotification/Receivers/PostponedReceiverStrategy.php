<?php

namespace App\Services\FcmNotification\Receivers;

use App\Helpers\Logger\FcmLogger;
use App\Models\Languages;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\FcmNotification\FcmNotyItemPayload;
use App\Services\FcmNotification\FcmNotyPayload;
use App\Services\FcmNotification\TemplateManager;
use App\Services\Telegram\TelegramDev;

class PostponedReceiverStrategy implements ReceiverStrategy
{
    private $payload;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->payload = new FcmNotyPayload();
        $this->userRepository = app(UserRepository::class);
    }

    public function getReceives(TemplateManager $templateManager): FcmNotyPayload
    {
        foreach($this->userRepository->getAdmins() as $admin){
            /** @var $admin User */
            $adminLang = $admin->lang ?? Languages::DEFAULT;
            $messagePayload = $templateManager->handle($adminLang);

            $this->payload->setItem(new FcmNotyItemPayload(
                $admin->fcm_token ?? null,
                Role::ROLE_ADMIN,
                $admin->id,
                $messagePayload
            ));

            FcmLogger::INFO("[ADMIN - {$admin->login}] сформированны данные для пуш уведомления");
//            TelegramDev::info("[ADMIN - {$admin->login}] сформированны данные для пуш уведомления");
        }

//        $this->payload->setItem($this->admin($templateManager));
        // @todo dev-telegram
//        TelegramDev::info("[ADMIN] сформированны данные для пуш уведомления");

        $this->payload->setItem($this->ps($templateManager));

        // формируем данные для tm
        foreach ($templateManager->model->user->dealer->tm ?? [] as $k => $tm) {
            $tmLang = $tm->lang ?? Languages::DEFAULT;

            $messagePayload = $templateManager->handle($tmLang);

            $this->payload->setItem(new FcmNotyItemPayload(
                $tm->fcm_token ?? null,
                Role::ROLE_TM,
                $tm->id,
                $messagePayload
            ));

            FcmLogger::INFO("[[$k] TM - {$tm->id}] сформированны данные для пуш уведомления [$tmLang]");
//            TelegramDev::info("[[$k] TM - {$tm->id}] сформированны данные для пуш уведомления [$tmLang]");
        }

        // формируем данные для pss
        foreach ($templateManager->model->reportMachines[0]->equipmentGroup->psss ?? [] as $k => $pss){
            $pssLang = $pss->lang ?? Languages::DEFAULT;
            $messagePayload = $templateManager->handle($pssLang);

            $this->payload->setItem(new FcmNotyItemPayload(
                $pss->fcm_token ?? null,
                Role::ROLE_TM,
                $pss->id,
                $messagePayload
            ));

            FcmLogger::INFO("[[$k] PSS - {$pss->id}] сформированны данные для пуш уведомления");
//            TelegramDev::info("[[$k] PSS - {$pss->id}] сформированны данные для пуш уведомления");
        }

        return $this->payload;
    }

    private function admin(TemplateManager $templateManager): FcmNotyItemPayload
    {
        $admin = $this->userRepository->getBy('login', 'admin');

        $adminLang = $admin->lang ?? Languages::DEFAULT;
        $messagePayload = $templateManager->handle($adminLang);

        return new FcmNotyItemPayload(
            $admin->fcm_token ?? null,
            'admin',
            $admin->id,
            $messagePayload
        );
    }

    private function ps(TemplateManager $templateManager): FcmNotyItemPayload
    {
        $ps = $templateManager->model->user;

        FcmLogger::INFO("[PS - {$ps->id}] сформированны данные для пуш уведомления FOR DEV");
//        TelegramDev::warn("[PS - {$ps->id}] сформированны данные для пуш уведомления FOR DEV");

        $psLang = $ps->lang ?? Languages::DEFAULT;
        $messagePayload = $templateManager->handle($psLang);

        return new FcmNotyItemPayload(
            $ps->fcm_token,
            'ps',
            $ps->id,
            $messagePayload
        );
    }
}

