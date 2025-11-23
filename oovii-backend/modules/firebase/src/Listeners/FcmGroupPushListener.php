<?php

namespace WezomCms\Firebase\Listeners;

use WezomCms\Firebase\Events\FcmGroupPush;
use WezomCms\Firebase\Receiver\ReceiverManager;
use WezomCms\Users\Models\User;

class FcmGroupPushListener extends BasePushListener
{
    public function handle(FcmGroupPush $event)
    {
        if(config('cms.firebase.firebase.firebase_use')){

            $template = $this->getTemplate($event);

            foreach ((new ReceiverManager($event->getType()))->get() as $receiver){
                /** @var $receiver User */
                $this->process($template, $event, $receiver);
            }
        }
    }
}

