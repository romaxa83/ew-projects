<?php

namespace App\Services\FcmNotification;

use App\Models\Notification\FcmTemplate;
use App\Services\FcmNotification\Exception\FcmNotificationException;
use App\Services\FcmNotification\Templates\TemplateStrategyParse;
use App\Services\Telegram\TelegramDev;
use Illuminate\Database\Eloquent\Model;

class TemplateManager
{
    private $strategy;
    /**
     * @var FcmTemplate
     */
    public $template;
    /**
     * @var Model
     */
    public $model;
    private $lang;

    public function __construct(
        FcmTemplate $template,
        Model $model,
        $lang = null
    )
    {
        $this->template = $template;
        $this->model = $model;
        $this->lang = $lang;
    }

    public function handle($lang = null): FcmMessagePayload
    {
        if($lang){
            $this->lang = $lang;
        }

        return $this->parseTemplate();
    }

    private function parseTemplate(): FcmMessagePayload
    {
        $str = $this->setTemplateParseStrategy($this->getStrategy());

        return $str->parse($this->template, $this->model);
    }

    private function getStrategy(): TemplateStrategyParse
    {
        $strategyName = lcfirst($this->template->type) . 'Strategy';
        $strategyClass = __NAMESPACE__ . '\\Templates\\' . ucwords($strategyName);

        throw_if(!class_exists($strategyClass), FcmNotificationException::class,
            "Класс не существует [{$strategyClass}]");

        // @todo dev-telegram
//        TelegramDev::info("Вызвана стратегия для парсинга шаблона {$strategyClass}");

        return new $strategyClass;
    }

    private function setTemplateParseStrategy(TemplateStrategyParse $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    private function parse(FcmTemplate $template, Model $model): FcmMessagePayload
    {
        return $this->strategy->parse($template, $model, $this->lang);
    }
}
