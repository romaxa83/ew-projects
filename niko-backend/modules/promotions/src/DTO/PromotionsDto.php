<?php

namespace WezomCms\Promotions\DTO;

use WezomCms\Core\DTO\AbstractDto;
use WezomCms\Promotions\Models\Promotions;

class PromotionsDto extends AbstractDto
{
    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toArray()
    {
        if(!$this->model){
            return [];
        }

        $promotion = $this->model;

        /** @var $promotion Promotions */
        return [
            'id' => $promotion->id,
            'isPersonal' => $promotion->isCommon() ? false : true,
            'title' => $promotion->name,
            'imageLink' => $promotion->getImage(),
            'webLink' => $promotion->link,
            'description' => $promotion->text,
        ];
    }
}

