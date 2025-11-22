<?php

namespace App\Services\Firebase;

use App\Events\Firebase\FcmPush;
use App\Models\Notification\Fcm;

final class FcmService
{
    public function createFromEvent(FcmPush $event): Fcm
    {
        try {
            $noty = new Fcm();
            $noty->user_id = $event->user->id;
            $noty->action = $event->action->getAction();
            $noty->send_data = $event->action->getMessageAsArray();
            $noty->type = $event->action->getType();
            if($event->relatedModel){
                $noty->entity_type = $event->relatedModel::class;
                $noty->entity_id = $event->relatedModel->id;
            }

            $noty->save();

            return $noty;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

