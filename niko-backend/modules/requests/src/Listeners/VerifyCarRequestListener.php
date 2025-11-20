<?php

namespace WezomCms\Requests\Listeners;

use WezomCms\Requests\Jobs\VerifyCarJob;
use WezomCms\Requests\Services\Request1CService;

class VerifyCarRequestListener
{
    public function handle($event)
    {

        dispatch(new VerifyCarJob($event->car));

//        $car = $event->car;
//        $request = \App::make(Request1CService::class)->verifyCar($car);

//        $car->is_verify = $request['car_status'];
//        $car->is_verify = true;
//        $car->save();
    }
}
