<?php

namespace WezomCms\Requests\Listeners;

use WezomCms\Requests\Jobs\OrderJob;
use WezomCms\Requests\Jobs\VerifyCarJob;

class OrderRequestListener
{
    public function handle($event)
    {
        dispatch(new OrderJob($event->order));
    }
}
