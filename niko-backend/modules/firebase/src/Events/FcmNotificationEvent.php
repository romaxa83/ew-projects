<?php

namespace WezomCms\Firebase\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\Firebase\Types\FcmNotificationType;
use WezomCms\Users\Models\User;

class FcmNotificationEvent
{
    use SerializesModels;

    public User $user;
    public array $data;
    public $type;
    public $orderId;

    public function __construct(User $user, array $data, $type = FcmNotificationType::NONE, $orderId = null)
    {
        $this->user = $user;
        $this->data = $data;
        $this->type = $type;
        $this->orderId = $orderId;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTitleMsg()
    {
        if(!isset($this->data['title'])){
            throw new \Exception("Для fcm уведомления, отсутствует title");
        }

        return $this->data['title'];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getBodyMsg()
    {
        if(!isset($this->data['body'])){
            throw new \Exception("Для fcm уведомления, отсутствует body");
        }

        return $this->data['body'];
    }
}
