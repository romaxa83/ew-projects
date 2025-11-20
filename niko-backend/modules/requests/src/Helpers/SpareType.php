<?php

namespace WezomCms\Requests\Helpers;

use WezomCms\Requests\Events\OrderRequest;
use WezomCms\Requests\Events\VerifyCarRequest;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Types\ServiceType;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Users\Models\Car;

class SpareType
{
    public static function check($type, $serviceId = null)
    {
        if(null != $serviceId && $type == ServiceType::TYPE_STO){
            $s = self::getService($serviceId);
            if($s->isTypeRepair()){
                return ServiceType::TYPE_REPAIRS;
            }
        }

        return $type;

    }

    private static function getService($id)
    {
        return Service::find($id);
    }
}
