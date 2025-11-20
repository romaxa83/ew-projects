<?php

namespace WezomCms\Firebase\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class NotificationListDto extends AbstractListDto
{
    protected NotificationDto $notificationDto;

    public function __construct()
    {
        $this->notificationDto = resolve(NotificationDto::class);
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {
        $this->data['notifications'] = [];
        if(!$this->collection && $this->collection->isEmpty()){
            return $this->data;
        }

        foreach ($this->collection as $key => $model){
            $this->data['notifications'][$key] = $this->notificationDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}

