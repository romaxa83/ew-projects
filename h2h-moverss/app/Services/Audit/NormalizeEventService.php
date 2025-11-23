<?php
//
//namespace App\Services\Audit;
//
//use App\Models\Audit;
//use App\Models\Client;
//use App\Models\Order;
//
//class NormalizeEventService
//{
//    /**
//     * todo дополнить тесты
//     * test @see \Tests\Unit\Services\Audit\NormalizeEventService\OrderEventTest
//     */
//    public function orderEvent(Audit $model): array
//    {
//        return [
//            'name' => $model->event,
//            'target' => $this->targetData()[$model->auditable_type] ?? $model->auditable_type,
//            'target_id' => $model->auditable_id,
//        ];
//    }
//
//    private function targetData(): array
//    {
//        return [
//            Order::MORPH_NAME => 'Order',
//            'App\Models\Order\Estimate' => 'Estimate',
//            'App\Models\Order\Estimate\Calculated' => 'Estimate-Calculated',
//            'App\Models\Order\Estimate\Interstate' => 'Estimate-Interstate',
//            'App\Models\Order\Estimate\Local' => 'Estimate-Local',
//            'App\Models\Order\Estimate\Intrastate' => 'Estimate-Intrastate',
//            'App\Models\Order\Work' => 'Work',
//            'App\Models\Order\Payment' => 'Payment',
//            'App\Models\Order\Material' => 'Material',
//            'App\Models\Order\CustomExtra' => 'CustomExtra',
//            'App\Models\Mailbox\Gmail\Message' => 'Gmail message',
//            'App\Models\Order\Waypoint' => 'Waypoint',
//            'App\Models\Order\WaypointNotes' => 'Waypoint notes',
//            'App\Models\Order\Inventory' => 'Inventory',
//            'App\Models\Attachment' => 'Attachment',
//            'App\Models\DispatchTruck' => 'DispatchTruck',
//            'App\Models\DispatchEmployer' => 'DispatchEmployee',
//            Client::MORPH_NAME => 'Client',
//            Client\Phone::MORPH_NAME => 'Client phone',
//            Client\Email::MORPH_NAME => 'Client email',
//            Client\Notes::MORPH_NAME => 'Client note',
//            Client\Messenger::MORPH_NAME => 'Client messenger',
//        ];
//    }
//}
