<?php

namespace WezomCms\Firebase\ModelFilters;

use EloquentFilter\ModelFilter;

class FcmNotificationFilter extends ModelFilter
{
    public function type($value)
    {
        $this->where('type', $value);
    }
}
