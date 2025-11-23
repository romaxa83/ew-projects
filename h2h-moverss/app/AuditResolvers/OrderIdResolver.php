<?php

namespace App\AuditResolvers;

use App\Models\Attachment;
use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;
use Illuminate\Support\Facades\Request;
use App\Models\Order;

class OrderIdResolver implements Resolver
{

    public static function resolve(Auditable $auditable = null)
    {

        if($auditable && (
                $auditable instanceof Order\Notes
                || $auditable instanceof Order\Payroll\Payroll
            )
        ) {
            return $auditable->order_id;
        }

        if($auditable && $auditable instanceof Order\Payroll\Item) {
            return $auditable->payroll->order_id;
        }


        // если клиент был отредактирован в заказе, привязываем к этой записи заказ
        $patternClient = '/^client\/profile\/save$/';
        if(preg_match($patternClient, Request::path())){
            return Request::input('order_id');
        }

        // привязываем назначение трака/сотрудника к заказу
        if(
            $auditable
            && ($auditable instanceof DispatchTruck || $auditable instanceof DispatchEmployer)
        ) {
            if(Request::has('start_date') && Request::has('works')) {
                return $auditable->work->order_id;
            }
        }

        // привязываем к заказу загрузку/удаление файлов
        if($auditable && $auditable instanceof Attachment) {
            // отработает при загрузке файлов
            if(
                Request::has('type')
                && Request::has('id')
                && Request::has('files')
                && Request::get('type') == 'order'
            ) {
                return Request::get('id');
            }
            // отработает при удалении файлов
            if(
                Request::has('hash')
            ) {
                if(!empty($auditable->miscs)){
                    if(
                        isset($auditable->miscs['object']['type'])
                        && $auditable->miscs['object']['type'] == 'order'
                        && isset($auditable->miscs['object']['id'])
                    ){
                        return $auditable->miscs['object']['id'];
                    }
                }
            }
        }

        if (Request::has('order_id')) {

            $patternCopy = '/^orders\/copy$/';
            if(preg_match($patternCopy, Request::path())){
                if($auditable instanceof Order) {
                    return $auditable->id;
                } elseif (array_key_exists('order_id', $auditable->getAttributes())) {
                    return $auditable->getAttributes()['order_id'];
                }

            }

            return Request::get('order_id');
        }
        if ($auditable instanceof Order) {

            return $auditable->id;
        }

        return null;
    }

}
