<?php

namespace WezomCms\Firebase\Templates\Strategies;

use App;
use WezomCms\Firebase\Messages\FcmMsgPayload;
use WezomCms\Firebase\Models\Template;
use WezomCms\Firebase\Templates\TemplateData;
use WezomCms\Users\Models\User;

abstract class AbstractStrategy
{
    protected Template $template;
    protected array $models;

    protected string $locale;
    protected array $payload = [];
    protected array $vars = [];

    public function __construct(Template $template, array $models = [])
    {
        $this->template = $template;
        $this->models = $models;

        $this->locale = $this->getLocale();

        $templateTranslation = $template->translations->where('locale', $this->locale)->first();

        $this->payload['title'] = $templateTranslation->title;
        $this->payload['text'] = $templateTranslation->text;

        $this->setVars();
    }

    private function getLocale(): string
    {
        if ($user = $this->getUserModel()) {
            return $user->lang;
        }

        return App::getLocale();
    }

    protected function getUserModel(): null|User
    {
        foreach ($this->models as $model) {
            if ($model instanceof User) {
                return $model;
            }
        }

        return null;
    }

    abstract protected function setVars(): void;

    public function parse(): TemplateData
    {
        foreach ($this->vars as $key => $value) {
            if (strpos($this->payload['title'], $key)) {
                $this->payload['title'] = str_replace('{' . $key . '}', $value, $this->payload['title']);
            }
            if (strpos($this->payload['text'], $key)) {
                $this->payload['text'] = str_replace('{' . $key . '}', $value, $this->payload['text']);
            }
        }

        return new FcmMsgPayload(
            $this->payload['title'],
            $this->payload['text'],
            $this->template->type
        );
    }
}

