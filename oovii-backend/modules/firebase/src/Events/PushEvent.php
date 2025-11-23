<?php

namespace WezomCms\Firebase\Events;

use Illuminate\Database\Eloquent\Model;

interface PushEvent
{
    public function getType(): string;
    public function getModel(): null|Model;
}
