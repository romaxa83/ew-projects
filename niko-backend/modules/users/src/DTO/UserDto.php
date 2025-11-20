<?php

namespace WezomCms\Users\DTO;

use WezomCms\Users\Models\User;
use WezomCms\Core\DTO\AbstractDto;

class UserDto extends AbstractDto
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

        $user = $this->model;

        /** @var $user User */
        return [
            'id' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
    }
}
