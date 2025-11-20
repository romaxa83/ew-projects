<?php

namespace WezomCms\Users\UseCase;

use WezomCms\Users\Models\Car;

class CarStatuses
{
    private Car $car;

    public function __construct(Car $car)
    {
        $this->car = $car;
    }

    public function forAdmin()
    {
        $html = $this->verify();

        return $html;
    }

    private function verify()
    {
        if($this->car->isVerify()){
            return '<span class="badge badge-success">'.__('cms-users::admin.car.status.verify').'</span>';
        }

        return '<span class="badge badge-danger">'.__('cms-users::admin.car.status.not verify').'</span>';
    }
}

