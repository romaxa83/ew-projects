<?php

namespace WezomCms\Users\DTO;

use WezomCms\Users\Models\User;
use WezomCms\Core\DTO\AbstractDto;

class LoyaltyDto extends AbstractDto
{
    private $link;
    private $privacyPolicy;
    private $termsOfUse;

    public function __construct()
    {
        $this->link = settings('settings.site.loyalty_link', null);
        $this->privacyPolicy = settings('settings.site.privacy_policy_link', null);
        $this->termsOfUse = settings('settings.site.terms_of_use_link', null);
    }

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
            'spending' => isset($user->loyalty->level_up_amount) ? $user->loyalty->getLevelUpAmount() : null,
            'isFamily' => isset($user->loyalty->loyalty_type) ? $user->loyalty->isFamilyType() : null,
            'webLink' => $this->link,
            'privacyPolicy' => $this->privacyPolicy,
            'termsOfUse' => $this->termsOfUse,
            'level' => $user->loyalty->loyalty_level ?? null,
            'levels' => \App::make(LoyaltyLevelListDto::class)
                ->setCollection(\WezomCms\Users\Models\LoyaltyLevel::query()->get())
                ->toList(),
        ];
    }
}

