<?php

namespace App\Listeners;

use App\Models\Audit;
use App\Models\DispatchTruck;
use App\Models\Order;
use OwenIt\Auditing\Events\Audited;

class AuditCreatedListener
{
    /**
     * Create the Audited event listener.
     */
    public function __construct()
    {
        // ...
    }

    /**
     * Handle the Audited event.
     *
     * @param \OwenIt\Auditing\Events\Audited $event
     * @return void
     */
    public function handle(Audited $event)
    {
        $this->ifClonedOrder($event);




//        $model = $event->model;
//        if($model instanceof DispatchTruck && empty($model->old_values)){
//            // когда на панели диспетчеров переключаются траки, он создает новую запись,
//            // приходиться костылить чтоб прокинуть старые значение в аудит из предыдущей записи
//
//            $audit = $event->audit;
//
//            $old = Audit::query()
//                ->where('order_id', $audit->order_id)
//                ->where('auditable_type', $audit->auditable_type)
//                ->where('id', '!=', $audit->id)
//                ->latest('created_at')
//                ->first();
//
//            if($old){
//                $audit->update([
//                    'event' => 'updated',
//                    'old_values' => $old->new_values,
//                ]);
//            }
//        }
    }

    public function ifClonedOrder($event): void
    {
        // если у нас произошло клонирование заказ, то мы в ручную
        // создаем два события о клонирования, для базового заказ и для
        // нового заказ чтоб потом вывести эти действия в логгере заказ

        if(
            $event->model instanceof Order
            && $event->model->base_id
        ){
            /** @var $audit Audit */
            $audit = $event->audit;

            $auditReplica = $audit->replicate();
            $auditReplica->created_at = $audit->created_at->subSecond();
            $auditReplica->updated_at = $audit->created_at->subSecond();
            $auditReplica->event = Audit::EVENT_CLONED;
            $auditReplica->new_values = [];
            $auditReplica->old_values = [
                'order_id' => $event->model->base_id
            ];

            $auditReplica->save();

            $auditReplicaOld = $audit->replicate();
            $auditReplicaOld->created_at = $audit->created_at->subSecond();
            $auditReplicaOld->updated_at = $audit->created_at->subSecond();
            $auditReplicaOld->event = Audit::EVENT_CLONED;
            $auditReplicaOld->order_id = $event->model->base_id;
            $auditReplicaOld->auditable_id = $event->model->base_id;
            $auditReplicaOld->old_values = [];
            $auditReplicaOld->new_values = [
                'order_id' => $event->model->id
            ];

            $auditReplicaOld->save();
        }
    }
}
