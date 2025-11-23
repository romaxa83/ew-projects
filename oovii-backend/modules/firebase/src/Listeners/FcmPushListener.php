<?php

namespace WezomCms\Firebase\Listeners;

use WezomCms\Firebase\Events\FcmPush;

class FcmPushListener extends BasePushListener
{
    public function handle(FcmPush $event)
    {
        if (config('cms.firebase.firebase.firebase_use')) {
            $template = $this->getTemplate($event);

            $this->process($template, $event, $event->getUser());
        }
    }
}
