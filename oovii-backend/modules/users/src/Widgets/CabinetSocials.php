<?php

namespace WezomCms\Users\Widgets;

use Auth;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;
use WezomCms\Users\Models\User;

class CabinetSocials extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        /** @var User $user */
        $user = Auth::user();

        $socialAccounts = $user->socialAccounts;

        $settings = settings('users.socials', []);

        $supportedSocials = collect(config('cms.users.users.supported_socials'))
            ->filter(function ($key) use ($settings) {
                return array_get($settings, "{$key}_id") !== null && array_get($settings, "{$key}_secret_key") !== null;
            })
            ->diff($socialAccounts->pluck('provider'));

        return compact('socialAccounts', 'supportedSocials');
    }
}
