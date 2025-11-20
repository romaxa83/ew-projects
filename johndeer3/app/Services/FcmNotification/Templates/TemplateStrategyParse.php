<?php

namespace App\Services\FcmNotification\Templates;

use App\Models\Notification\FcmTemplate;
use Illuminate\Database\Eloquent\Model;

interface TemplateStrategyParse
{
    public function parse(FcmTemplate $template, Model $model, $lang = null): Templatable;
}
