<?php

namespace WezomCms\Users\Widgets;

use Lang;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class CabinetAuthSocials extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $supported = config('cms.users.users.supported_socials', []);

        $settings = settings('users.socials', []);

        $socials = [];
        foreach ($supported as $key) {
            if (array_get($settings, "{$key}_id") !== null && array_get($settings, "{$key}_secret_key") !== null) {
                $socials[$key] = Lang::get("cms-users::site.{$key}");
            }
        }

        if (empty($socials)) {
            return null;
        }

        return compact('socials');
    }
}
