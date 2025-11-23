<?php

namespace WezomCms\Firebase\Listeners;

use Throwable;
use WezomCms\Firebase\Dto\FcmDto;
use WezomCms\Firebase\Events\PushEvent;
use WezomCms\Firebase\Exceptions\FcmPushException;
use WezomCms\Firebase\Messages\FcmPayload;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Firebase\Models\Template;
use WezomCms\Firebase\Repositories\TemplateRepository;
use WezomCms\Firebase\Services\FcmService;
use WezomCms\Firebase\Services\Sender\FirebaseSender;
use WezomCms\Firebase\Templates\TemplateManager;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Models\User;

class BasePushListener
{
    public function __construct(
        protected TemplateRepository $templateRepository,
        protected FcmService $fcmService,
        protected FirebaseSender $sender
    ) {
    }

    /**
     * @param PushEvent $event
     * @return Template
     * @throws FcmPushException
     */
    protected function getTemplate(PushEvent $event): Template
    {
        /** @var $template Template */
        $template = $this->templateRepository->getOneBy('type', $event->getType(), ['translations'], true);

        if (!$template) {
            throw new FcmPushException(
                __(
                    'cms-firebase::admin.exception.not_found_template',
                    [
                        'template' => $event->getType()
                    ]
                )
            );
        }

        return $template;
    }

    protected function process(Template $template, PushEvent $event, User $user)
    {
        $payload = (new TemplateManager($template, $user, $event->getModel()))->handle();

        $dto = FcmDto::byArgs(
            [
                'user_id' => $user->id,
                'entity_type' => $event->getModel() ? $event->getModel()::class : null,
                'entity_id' => $event->getModel() ? $event->getModel()->getKey() : null,
                'type' => $event->getType(),
                'status' => FcmNotification::STATUS_CREATED,
                'send_data' => $payload
            ]
        );

        $fcm = $this->fcmService->create($dto);

        try {
            if (!$user->fcm_token) {
                throw new FcmPushException("User [{$user->id}] not have fcm_token");
            }
            $result = $this->sender->send(
                new FcmPayload(
                    $payload,
                    $user->fcm_token,
                )
            );

            $fcm->setSendStatus($result);

            Telegram::info('📨📨 Send push notification', $user->name, Telegram::LEVEL_IMPORTANT);
        } catch (FcmPushException $e) {
            logger($e->getMessage());
            $fcm->setError($e->getMessage());
            Telegram::error($e, $user->name);
        } catch (Throwable $e) {
            logger($e->getMessage());
        }
    }
}

