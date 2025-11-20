<?php

namespace App\Services\FcmNotification\Templates;

use App\Models\Languages;
use App\Models\Notification\FcmTemplate;
use App\Services\FcmNotification\Exception\FcmNotificationException;
use App\Services\FcmNotification\FcmMessagePayload;
use Illuminate\Database\Eloquent\Model;

// парсим шаблон на переменый
class PlannedStrategy implements TemplateStrategyParse
{
    private $lang;

    public function parse(FcmTemplate $template, Model $model, $lang = null): Templatable
    {
        $this->lang = $lang;
        if(null === $this->lang){
            $this->lang = $model->user->lang ?? Languages::DEFAULT;
        }

        $templateTran = $template->translations->firstWhere('lang', $this->lang);

        if(null === $templateTran){
            $templateTran = $template->translations->firstWhere('lang', Languages::DEFAULT);
        }

        if(null === $templateTran){
            throw new FcmNotificationException("Для шаблона [{$template->type}] нет переводов по языку [{$this->lang}]");
        }

        $payload['title'] = $templateTran->title;
        $payload['text'] = $templateTran->text;

        $vars['dealer'] = $this->getDealerName($model);
        $vars['date_time'] = $this->getDateTime($model);
        $vars['eq_type'] = $this->getEgName($model);
        $vars['model_name'] = $this->getModelName($model);
        $vars['client'] = $this->getClientName($model);
        $vars['client_oblast'] = $this->getClientOblast($model);
        $vars['client_rayon'] = $this->getClientRayon($model);

        foreach ($vars as $key => $value){
            if(strpos($payload['title'], $key)){
                $payload['title'] = str_replace('{'.$key.'}', $value, $payload['title']);
            }
            if(strpos($payload['text'], $key)){
                $payload['text'] = str_replace('{'.$key.'}', $value, $payload['text']);
            }
        }

        return new FcmMessagePayload($payload['title'], $payload['text'], $template->type);
    }

    private function getDealerName($model): string
    {
        return $model->user->dealer->name ?? '';
    }

    private function getDateTime($model): string
    {
        return $model->pushData->planned_at ?? '';
    }

    private function getEgName($model): string
    {
        return $model->reportMachines[0]->equipmentGroup->name ?? '';
    }

    private function getModelName($model): string
    {
        return $model->reportMachines[0]->modelDescription->name ?? '';
    }

    private function getClientName($model): string
    {
        return isset($model->clients[0]) ? $model->clients[0]->full_name : '';
    }

    private function getClientOblast($model): string
    {
        return $model->clients[0]->region->name ?? '';
    }

    private function getClientRayon($model): string
    {
        return  '';
    }
}
