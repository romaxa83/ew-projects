<?php

namespace WezomCms\Users\Widgets;

use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class CabinetButton extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        return ['user' => \Auth::user()];
    }
}
