<?php

namespace App\Services\FcmNotification;

use App\Helpers\Logger\FcmLogger;
use App\Services\FcmNotification\Exception\FcmNotificationException;
use App\Services\FcmNotification\Receivers\ReceiverStrategy;
use App\Services\Telegram\TelegramDev;

class PushDataManager
{
    private $strategy;
    /**
     * @var TemplateManager
     */
    private $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
    }

    public function handleForGroup(): FcmNotyPayload
    {
        return $this->getReceivers();
    }

    private function getReceivers(): FcmNotyPayload
    {
        $obj = $this->setReceiverStrategy($this->getStrategy());

        return $obj->receives($this->templateManager);
    }

    private function getStrategy(): ReceiverStrategy
    {
        $strategyName = lcfirst($this->templateManager->template->type) . 'ReceiverStrategy';
        $strategyClass = __NAMESPACE__ . '\\Receivers\\' . ucwords($strategyName);

        throw_if(!class_exists($strategyClass), FcmNotificationException::class,
            "Класс не существует [{$strategyClass}]");

        FcmLogger::INFO("Вызвана стратегия для получение получателей пуша {$strategyClass}");
//        TelegramDev::info("Вызвана стратегия для получение получателей пуша {$strategyClass}");

        return new $strategyClass;
    }

    private function setReceiverStrategy(ReceiverStrategy $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    private function receives(TemplateManager $templateManager): FcmNotyPayload
    {
        return $this->strategy->getReceives($templateManager);
    }
}

