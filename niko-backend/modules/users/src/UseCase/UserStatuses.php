<?php

namespace WezomCms\Users\UseCase;

use WezomCms\Users\Models\User;
use WezomCms\Users\Types\LoyaltyLevel;
use WezomCms\Users\Types\LoyaltyType;

class UserStatuses
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function forAdmin()
    {
        $html = $this->verify();
        $html .= $this->loyaltyType();
        $html .= $this->loyaltyLevel();


        return $html;
    }

    private function verify()
    {
        if($this->user->isVerify()){
            return '<span class="badge badge-success">'.__('cms-users::admin.car.status.verify').'</span>';
        }

        return '<span class="badge badge-danger">'.__('cms-users::admin.car.status.not verify').'</span>';
    }

    private function loyaltyType()
    {
        if($this->user->loyalty){

            if($this->user->loyalty->hasLoyaltyType()){
                return '<br><span class="badge badge-success">'. LoyaltyType::getName($this->user->loyalty->loyalty_type) . '</span>';
            }
            return '<br><span class="badge badge-danger">'. LoyaltyType::getName($this->user->loyalty_type) .'</span>';
        }

    }

    private function loyaltyLevel()
    {
        if($this->user->loyalty){
            return '<br><span class="badge badge-info">'. LoyaltyLevel::getName($this->user->loyalty->loyalty_level) .'</span>';
        }


    }
}
