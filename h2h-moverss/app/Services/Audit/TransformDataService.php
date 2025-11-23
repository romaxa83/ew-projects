<?php

namespace App\Services\Audit;

use App\Enums\Catalog\BuildingTypeEnum;
use App\Enums\Catalog\FlightTypeEnum;
use App\Enums\Catalog\MoveSizeTypeEnum;
use App\Enums\Catalog\ParkingTypeEnum;
use App\Models\Attachment;
use App\Models\Audit;
use App\Models\BuildingType;
use App\Models\Client;
use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use App\Models\Division;
use App\Models\Employee;
use App\Models\MoveSize;
use App\Models\Order;
use App\Models\Order\Material;
use App\Models\Order\Source;
use App\Models\Order\Status;
use App\Models\Order\WorkDispatchTouch;
use App\Models\ParkingType;
use App\Models\PaymentAccount;
use App\Models\Settings\WaypointFlights;
use App\Models\Truck\Truck;
use App\Models\User\Role;
use App\User;
use Carbon\Carbon;

class TransformDataService
{
    // список сущностей для которых все измененные значения выводятся
    private function entitiesShowFields(): array
    {
        return [
            Order::MORPH_NAME,
            Order\Estimate::MORPH_NAME,
            Order\Estimate\Interstate::MORPH_NAME,
            Order\Estimate\Local::MORPH_NAME,
            Order\Estimate\Calculated::MORPH_NAME,
            Order\Work::MORPH_NAME,
            Order\Waypoint::MORPH_NAME,
            Order\Material::MORPH_NAME,
            Order\CustomExtra::MORPH_NAME,
            Order\Payment::MORPH_NAME,
            Order\Inventory::MORPH_NAME,
            Order\Payroll\Payroll::MORPH_NAME,
            Order\Payroll\Item::MORPH_NAME,
            Attachment::MORPH_NAME,
        ];
    }

    private function changeNamingField(): array
    {
        return [
            'lname' => 'last name',
            'division_id' => 'division',
            'type_id' => 'type',
            'user_id' => 'user',
            'client_id' => 'client',
            'move_size_id' => 'move size',
            'source_id' => 'source',
            'workTypes' => 'work types',
            'status_id' => 'status',
            'building_type_id' => 'building type',
            'parking_type_id' => 'parking type',
            'ap' => 'unit',
            'flights_id' => 'stairs',
            'sizing_is_auto' => 'auto size',
            'discount_value' => 'discount',
            'payment_account_id' => 'method',
        ];
    }

    // исключение полей для всех записей
    private function excludeFields(): array
    {
        return [
            'id',
            'hash',
            'sort',
            'lat',
            'lng',
            'random_ref',
        ];
    }

    // игнорирование изменения для сущностей
    private function excludeEntities(): array
    {
        return [
            Order\Extended::MORPH_NAME,
        ];
    }

    // исключение полей для конкретных сущностей
    private function excludeFieldsByEntity(): array
    {
        return [
            Client\Email::MORPH_NAME => [
                'client_id'
            ],
            Client\Phone::MORPH_NAME => [
                'client_id'
            ],
            Client\Messenger::MORPH_NAME => [
                'client_id'
            ],
            Client\Notes::MORPH_NAME => [
                'client_id',
                'user_id',
            ],
            Order::MORPH_NAME => [
                'updated_by',
                'first_calc_as_client',
            ],
            Order\Notes::MORPH_NAME => [
                'user_id',
                'order_id',
            ],
            Order\Extended::MORPH_NAME => [
                'host',
                'form_id',
            ],
            Order\Work::MORPH_NAME => [
                'notes_created_at',
            ],
            Order\Waypoint::MORPH_NAME => [
                'order_id',
                'miscs',
            ],
            Order\WaypointNotes::MORPH_NAME => [
                'waypoint_id',
                'user_id',
            ],
            Order\Estimate::MORPH_NAME => [
                'fee_type',
            ],
            Order\Estimate\Calculated::MORPH_NAME => [
                'order_id',
            ],
            Order\Payment::MORPH_NAME => [
                'user_id',
                'order_id',
            ],
            Order\Inventory::MORPH_NAME => [
                'item_id',
                'section_id',
                'order_id',
                'is_section',
            ],
            Order\Material::MORPH_NAME => [
                'title',
                'material_id',
                'type_id',
                'price',
                'packing_price',
                'order_id',
                'unpacking_price',
            ],
            Order\CustomExtra::MORPH_NAME => [
                'title',
                'order_id',
            ],
            Order\Payroll\Payroll::MORPH_NAME => [
                'order_id',
            ],
            Order\Payroll\Item::MORPH_NAME => [
                'payroll_id',
                'employee_id',
            ],
            Attachment::MORPH_NAME => [
                'user_id',
            ],
            DispatchTruck::MORPH_NAME => [
                'work_id',
            ],
            DispatchEmployer::MORPH_NAME => [
                'work_id',
            ],
        ];
    }

    private function changeNamingByEntity(): array
    {
        return [
            Client\Email::MORPH_NAME => [
                'value' => 'email',
            ],
            Client\Phone::MORPH_NAME => [
                'value' => 'phone',
                'type' => 'phone type'
            ],
            Client\Messenger::MORPH_NAME => [
                'value' => 'messenger',
                'type' => 'messenger type'
            ],
            Client\Notes::MORPH_NAME => [
                'value' => 'note',
            ],
            Order\Work::MORPH_NAME => [
                'trucks' => 'trucks qty',
                'employees' => 'employees qty',
            ],
            Order\WaypointNotes::MORPH_NAME => [
                'value' => 'note',
            ],
            Order\Estimate::MORPH_NAME => [
                'type' => 'move type',
            ],
            Order\Notes::MORPH_NAME => [
                'text' => 'note',
            ],
            Order\Payment::MORPH_NAME => [
                'in_total_sum' => 'in total',
            ],
            DispatchTruck::MORPH_NAME => [
                'truck_id' => 'truck',
            ],
            DispatchEmployer::MORPH_NAME => [
                'employee_id' => 'employee',
            ],
            Order\Payroll\Payroll::MORPH_NAME => [
                'processed_employee_id' => 'processed employee',
            ],
            Order\Payroll\Item::MORPH_NAME => [
                'employee_id' => 'employee',
                'role_id' => 'role',
            ],
        ];
    }

    // исключение полей для конкретных сущностей, если запись удалены
    private function excludeFieldsByEntityEventDeleted(): array
    {
        return [
            Client\Phone::MORPH_NAME => [
                'type_id',
                'is_primary',
                'client_id',
            ],
            Client\Email::MORPH_NAME => [
                'type_id',
                'is_primary',
                'client_id',
            ],
            Client\Messenger::MORPH_NAME => [
                'client_id',
                'type_id',
            ],
            Client\Notes::MORPH_NAME => [
                'client_id',
                'user_id',
            ],
            Order\Waypoint::MORPH_NAME => [
                'order_id',
            ],
            Order\WaypointNotes::MORPH_NAME => [
                'user_id',
                'waypoint_id',
            ],
            Order\Notes::MORPH_NAME => [
                'order_id',
                'user_id',
                'visibility',
                'is_pinned',
            ],
            Order\Inventory::MORPH_NAME => [
                'is_section',
                'price',
                'qty',
                'weight',
                'volume',
                'item_id',
                'section_id',
                'order_id',
            ],
            Order\Material::MORPH_NAME => [
                'title',
                'material_id',
                'type_id',
                'need_packing',
                'need_unpacking',
                'packing_price',
                'unpacking_price',
                'order_id',
            ],
            Order\CustomExtra::MORPH_NAME => [
                'title',
                'order_id',
            ],
            DispatchTruck::MORPH_NAME => [
                'work_id',
            ],
            DispatchEmployer::MORPH_NAME => [
                'work_id',
            ],
        ];
    }

    private function entitiesName(Audit $model): string
    {

        if ($model->auditable_type == Order\Payroll\Item::MORPH_NAME){
            $model->load(['auditable.employee']);
            $employeeName = $model->auditable->employee->full_name ?? null;
            return "Payroll mover ($employeeName)";
        }

        if($model->auditable_type == Order\Waypoint::MORPH_NAME){
            $type = $model->auditable->type ?? null;
            return "Waypoint ($type)";
        }
        if($model->auditable_type == Order\WaypointNotes::MORPH_NAME){
            $model->load(['auditable.waypoint']);
            $type = $model->auditable->waypoint->type ?? null;
            return "Waypoint ($type) note";
        }
        if($model->auditable_type == Order\Inventory::MORPH_NAME){
            $name = null;
            if(isset($model->old_values['title'])){
                $name = $model->old_values['title'];
            }
            if(isset($model->new_values['title'])){
                $name = $model->new_values['title'];
            }
            if(is_null($name)){
                $model->load(['auditable']);
                $name = $model->auditable->title ?? null;
            }
            return "Inventory ($name)";
        }
        if($model->auditable_type == Order\Material::MORPH_NAME){

            $name = null;
            if(isset($model->old_values['title'])){
                $name = $model->old_values['title'];
            }
            if(isset($model->new_values['title'])){
                $name = $model->new_values['title'];
            }
            if(is_null($name)){
                $model->load(['auditable']);
                $name = $model->auditable->title ?? null;
            }

            return "Material ($name)";
        }
        if($model->auditable_type == Order\CustomExtra::MORPH_NAME){
            $name = null;
            if(isset($model->old_values['title'])){
                $name = $model->old_values['title'];
            }
            if(isset($model->new_values['title'])){
                $name = $model->new_values['title'];
            }
            if(is_null($name)){
                $model->load(['auditable']);
                $name = $model->auditable->title ?? null;
            }
            return "Material custom ($name)";
        }

        if(
            $model->auditable_type == DispatchTruck::MORPH_NAME
        ){
            $orderId = null;
            if(isset($model->old_values['work_id'])){
                $orderId = Order\Work::find($model->old_values['work_id'])->order_id ?? null;
            } elseif (isset($model->new_values['work_id'])) {
                $orderId = Order\Work::find($model->new_values['work_id'])->order_id ?? null;
            }

            return "Dispatch truck (by order - #$orderId)";
        }

        if(
            $model->auditable_type == DispatchEmployer::MORPH_NAME
        ){
            $orderId = null;
            if(isset($model->old_values['work_id'])){
                $orderId = Order\Work::find($model->old_values['work_id'])->order_id ?? null;
            } elseif (isset($model->new_values['work_id'])) {
                $orderId = Order\Work::find($model->new_values['work_id'])->order_id ?? null;
            }

            return "Dispatch employee (by order - #$orderId)";
        }

        $data = [
            Client::MORPH_NAME => 'Client',
            Client\Phone::MORPH_NAME => 'Client',
            Client\Email::MORPH_NAME => 'Client',
            Client\Notes::MORPH_NAME => 'Client',
            Client\Messenger::MORPH_NAME => 'Client',
            Order::MORPH_NAME => 'Order',
            Order\Notes::MORPH_NAME => 'Order',
            Order\Work::MORPH_NAME => 'Service',
            Order\Estimate::MORPH_NAME => 'Estimate',
            Order\Estimate\Interstate::MORPH_NAME => 'Estimate (interstate)',
            Order\Estimate\Intrastate::MORPH_NAME => 'Estimate (intrastate)',
            Order\Estimate\Local::MORPH_NAME => 'Estimate (local)',
            Order\Estimate\Calculated::MORPH_NAME => 'Estimate (calculated)',
            Order\Payment::MORPH_NAME => 'Payment',
            Order\Payroll\Payroll::MORPH_NAME => 'Payroll',
            Order\Payroll\Item::MORPH_NAME => 'Payroll mover',
            Attachment::MORPH_NAME => 'File',
        ];

        return $data[$model->auditable_type] ?? $model->auditable_type;
    }

    private function getAction($data, Audit $model): string
    {
        if($model->event == Audit::EVENT_CLONED){
            return Audit::EVENT_CLONED;
        }

        if($model->auditable_type == Order\Payroll\Payroll::MORPH_NAME){
            return $model->event;
        }

        if(!is_null($data['old']) && is_null($data['new'])){
            return Audit::EVENT_DELETED;
        }
        if(is_null($data['old']) && !is_null($data['new'])){
            return Audit::EVENT_CREATED;
        }

        return Audit::EVENT_UPDATED;
    }

    private function boolFields(): array
    {
        return [
            'is_auto',
            'is_section',
            'has_elevator',
            'sizing_is_auto',
            'auto_size',
            'auto size',
            'in_dispatch',
            'need_packing',
            'need_unpacking',
            'is_primary',
            'shuttle_pickup',
            'shuttle_delivery',
            'is_pinned',
            'calculated_moving_distance_is_auto',
            'in total',
            'is processed',
            'is_processed',
            'is_cc_due',
        ];
    }

    private function dateFields(): array
    {
        return [
            'processed_at',
        ];
    }

    public function forOrder(Audit $model): array
    {
        $res = $this->fieldChanges($model);

        if(empty($res)){
            return $res;
        }

        if(in_array($model->auditable_type, $this->entitiesShowFields())){

            return  [
                [
                    'audit_id' => $model->id,
                    'action' => $this->getAction(current($res), $model),
                    'entity' => $this->entitiesName($model),
                    'details' => $res,
                    'created_at' => $model->created_at->timestamp,
                    'is_client_activity' => $model->is_client_activity,
                    'user' => $model->user,
                    'client' => $model->is_client_activity
                        ? $model->client
                        : null
                ]
            ];
        } else {
            $tmp = [];

            foreach ($res as $item){
                $tmp[] = [
                    'audit_id' => $model->id,
                    'action' => $this->getAction($item, $model),
                    'entity' => $this->entitiesName($model),
                    'details' => [$item],
                    'created_at' => $model->created_at->timestamp,
                    'is_client_activity' => $model->is_client_activity,
                    'user' => $model->user,
                    'client' => $model->is_client_activity
                        ? $model->client
                        : null
                ];
            }

//            dd($tmp);

            return $tmp;
        }
    }

    public function forDispatch(Audit $model): array
    {
        $res = $this->fieldChanges($model);

        if(empty($res)){
            return $res;
        }

        $tmp = [];
        foreach ($res as $item){

            $tmp[] = [
                'action' => $this->getAction($item, $model),
                'entity' => $this->entitiesName($model),
                'details' => [$item],
                'created_at' => $model->created_at->timestamp,
                'is_client_activity' => $model->is_client_activity,
                'user' => $model->user,
                'client' => $model->is_client_activity
                    ? $model->client
                    : null
            ];
        }

        return $tmp;
    }

    public function fieldChanges(Audit $model): array
    {
        if(in_array( $model->auditable_type, $this->excludeEntities())){
            return [];
        }

        $data = $model->getPrettyValues();

        $t = array_column($data, 'field');
        if(
            isset($t[0])
            && isset($t[1])
            && (
                ($t[0] == 'tags' && $t[1] == 'custom_tags')
                || ($t[0] == 'workTypes' && $t[1] == 'custom_work_types')
            )
        ){
            $custom = $data[1];
            unset($data[1]);
            $data[0] = $custom;
        }

        // если лог о добавлен файл, оставляем только название файла
        if($model->isAttachment()){
            $data = $this->dataForAttachment($model, $data);
        }

        foreach ($data as $key => $value) {
            if(in_array($value['field'], $this->excludeFields())) {
                unset($data[$key]);
                continue;
            }

            if($model->isEventDeleted()) {
                if(key_exists($model->auditable_type, $this->excludeFieldsByEntityEventDeleted())) {
                    if(in_array($value['field'], $this->excludeFieldsByEntityEventDeleted()[$model->auditable_type])) {
                        unset($data[$key]);
                        continue;
                    }
                }

            } else {
                if(key_exists($model->auditable_type, $this->excludeFieldsByEntity())) {
                    if(in_array($value['field'], $this->excludeFieldsByEntity()[$model->auditable_type])) {
                        unset($data[$key]);
                        continue;
                    }
                }
            }

            if(key_exists($value['field'], $this->changeNamingField())) {
                $data[$key]['field'] = $this->changeNamingField()[$value['field']];
                $value['field'] = $this->changeNamingField()[$value['field']];
            }

            if(key_exists($model->auditable_type, $this->changeNamingByEntity())) {
                $data[$key]['field'] = $this->changeNamingByEntity()[$model->auditable_type][$value['field']] ?? $value['field'];
                $value['field'] = $this->changeNamingByEntity()[$model->auditable_type][$value['field']] ?? $value['field'];
            }

            if(in_array($value['field'], $this->boolFields())) {
                $data[$key] = $this->bool($value);
            }
            if(in_array($value['field'], $this->dateFields())) {
                $data[$key] = $this->formatDate($value);
            }
//            dd($value);
            //
            if($value['field'] == 'phone type'){
                $data[$key] = $this->phoneType($value);
            }
            if($value['field'] == 'messenger type'){
                $data[$key] = $this->messengerType($value);
            }
            if($value['field'] == 'tags'){
                $data[$key] = $this->tags($value);
            }
            if($value['field'] == 'custom_tags'){
                $data[$key] = $this->customTags($value);
            }
            if($value['field'] == 'user'){
                $data[$key] = $this->user($value);
            }
            if($value['field'] == 'client'){
                $data[$key] = $this->client($value);
            }
            if($value['field'] == 'move size'){
                $data[$key] = $this->moveSize($value);
            }
            if($value['field'] == 'source'){
                $data[$key] = $this->source($value);
            }
            if($value['field'] == 'source'){
                $data[$key] = $this->source($value);
            }
            if($value['field'] == 'work types'){
                $data[$key] = $this->workTypes($value);
            }
            if($value['field'] == 'custom_work_types'){
                $data[$key] = $this->customWorkTypes($value);
            }
            if($value['field'] == 'status'){
                $data[$key] = $this->status($value);
            }
            if($value['field'] == 'building type'){
                $data[$key] = $this->buildingType($value);
            }
            if($value['field'] == 'parking type'){
                $data[$key] = $this->parkingType($value);
            }
            if($value['field'] == 'stairs'){
                $data[$key] = $this->flights($value);
            }
            if($value['field'] == 'method'){
                $data[$key] = $this->paymentAccount($value);
            }
            if($value['field'] == 'division'){
                $data[$key] = $this->division($value);
            }
            if($value['field'] == 'paid_form_bol'){
                // payroll
                unset($data[$key]);
                $data['paid_form_bol'] = $this->paidFromBol($value);
            }
            if($value['field'] == 'processed employee'){
                $data[$key] = $this->employee($value);
            }
            if($value['field'] == 'role'){
                $data[$key] = $this->role($value);
            }

            // dispatch
            if($value['field'] == 'truck'){
                $data[$key] = $this->truck($value);
            }
            if($value['field'] == 'truck_ids'){
                $data[$key] = $this->trucks($value);
            }
            if($value['field'] == 'dispatch_work'){
                $data[$key] = $this->workDispatch($value);
            }
            if($value['field'] == 'employee'){
                $data[$key] = $this->employee($value);
            }
            if($value['field'] == 'employee_ids'){
                $data[$key] = $this->employees($value);
            }

            if($value['new'] === null && $value['old'] === null) {
                unset($data[$key]);
            }
            if($value['new'] == $value['old']) {
                unset($data[$key]);
            }
            if(
                ($value['field'] === 'need_packing'
                && $value['old'] === null
                && $value['new'] === 0)
                ||
                ($value['field'] === 'need_unpacking'
                    && $value['old'] === null
                    && $value['new'] === 0)
            ) {
                unset($data[$key]);
            }
        }


        if(array_key_exists('paid_form_bol', $data)){
            $paidData = $data['paid_form_bol'];
            unset($data['paid_form_bol']);
            foreach ($paidData as $value) {
                $data[] = $value;
            }
        }


        foreach ($data as $key => $value) {
            $data[$key]['field'] = str_replace("_", " ", $value['field']);
        }

        return array_values($data);
    }

    private function dataForAttachment(Audit $model, $data): array
    {
        $excludeFields = [
            'patch',
            'size',
        ];

        foreach ($data as $key => $value) {
            if($value['field'] == 'miscs'){
                unset($data[$key]);
            }
        }

        if($model->isEventDeleted()){
            if(isset($model->old_values['miscs']) && !empty($model->old_values['miscs'])) {
                $tmp = json_decode($model->old_values['miscs'], true);
                if(isset($tmp['file']) && !empty($tmp['file'])) {
                    $data = [];
                    foreach ($tmp['file'] as $k => $v) {
                        if(in_array($k, $excludeFields)) {
                            continue;
                        }

                        $data[] = [
                            'field' => $k,
                            'new' => null,
                            'old' =>$v,
                        ];
                    }
                }
            }
        } else {
            if(isset($model->new_values['miscs']) && !empty($model->new_values['miscs'])) {
                $tmp = json_decode($model->new_values['miscs'], true);

                if(isset($tmp['file']) && !empty($tmp['file'])) {
                    foreach ($tmp['file'] as $k => $v) {
                        if(in_array($k, $excludeFields)) {
                            continue;
                        }

                        $data[] = [
                            'field' => $k,
                            'new' => $v,
                            'old' => null,
                        ];
                    }
                }
            }
        }

        return array_values($data);
    }

    private function bool(array $data): array
    {
        return [
            'field' => $data['field'],
            'new' => $this->boolCheck($data['new']),
            'old' => $this->boolCheck($data['old']),
        ];
    }

    private function formatDate(array $data): array
    {
        if($data['new']){
            $newDate = Carbon::createFromFormat('Y-m-d H:i:s', $data['new'], 'UTC')
                ->setTimezone('America/Chicago')
                ->toDateTimeString();
        }
        if($data['old']){
            $oldDate = Carbon::createFromFormat('Y-m-d H:i:s', $data['old'], 'UTC')
                ->setTimezone('America/Chicago')
                ->toDateTimeString();
        }

        return [
            'field' => $data['field'],
            'new' => $newDate ?? null,
            'old' => $oldDate ?? null,
        ];
    }

    private function paidFromBol(array $data): array
    {
        $exclude = [
            'credit_card_clean',
            'check',
            'tips',
        ];
        $tmp = [];

        if(!is_null($data['new'])) {
            $paidData = json_to_array(
                json_decode($data['new'], true, 512, JSON_THROW_ON_ERROR)
            );
            foreach ($paidData as $key => $value) {
                if(!in_array($key, $exclude)) {
                    $tmp[$key] = [
                        'field' => $key,
                        'new' => $value == "" ? 0 : $value,
                        'old' => null,
                    ];
                }
            }
        }
        if(!is_null($data['old'])) {
            $paidData = json_to_array(
                json_decode($data['old'], true, 512, JSON_THROW_ON_ERROR)
            );

            foreach ($paidData as $key => $value) {
                if(!in_array($key, $exclude)) {
                    if(isset($tmp[$key]) && is_null($tmp[$key]['old'])){
                        $tmp[$key]['old'] = $value == "" ? 0 : $value;
                    } else {
                        $tmp[$key] = [
                            'field' => $key,
                            'new' => null,
                            'old' => $value == "" ? 0 : $value,
                        ];
                    }
                }
            }
        }

        if(
            isset($tmp['cash'])
            && !is_null($tmp['cash']['old'])
            && !is_null($tmp['cash']['new'])
        ){
            return [
                [
                    'field' => 'cash',
                    'new' => $tmp['cash']['new'],
                    'old' => $tmp['cash']['old'],
                ],
            ];
        }

        return $tmp;
    }

    private function boolCheck($value): ?string
    {
        if($value === 1 || $value === '1' || $value === true) {
            return 'true';
        }
        if($value === 0 || $value === '0' || $value === false) {
            return 'false';
        }

        return null;
    }

    private function status(array $data): array
    {
        $items = Status::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function source(array $data): array
    {
        $items = Source::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function moveSize(array $data): array
    {
        return [
            'field' => $data['field'],
            'new' => MoveSizeTypeEnum::getLabelAsNameByValue($data['new']),
            'old' => MoveSizeTypeEnum::getLabelAsNameByValue($data['old']),
        ];
    }

    private function paymentAccount(array $data): array
    {
        $items = PaymentAccount::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function division(array $data): array
    {
        $items = Division::query()
            ->select(['name', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('name', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function buildingType(array $data): array
    {
        return [
            'field' => $data['field'],
            'new' => BuildingTypeEnum::getLabelAsNameByValue($data['new']),
            'old' => BuildingTypeEnum::getLabelAsNameByValue($data['old']),
        ];
    }

    private function parkingType(array $data): array
    {
        return [
            'field' => $data['field'],
            'new' => ParkingTypeEnum::getLabelAsNameByValue($data['new']),
            'old' => ParkingTypeEnum::getLabelAsNameByValue($data['old']),
        ];
    }

    private function workTypes(array $data): array
    {
        $new = [];
        foreach ($data['new'] as $value) {
            $new[] = $value['title'];
        }
        $old = [];
        foreach ($data['old'] as $value) {
            $old[] = $value['title'];
        }

        return [
            'field' => $data['field'],
            'new' => !empty($new)
                ? implode(', ', $new)
                : null,
            'old' => !empty($old)
                ? implode(', ', $old)
                : null,
        ];
    }

    private function customWorkTypes(array $data): array
    {
        return [
            'field' => 'work_types',
            'new' => !empty($data['new'])
                ? implode(', ', $data['new'])
                : null,
            'old' => !empty($data['old'])
                ? implode(', ', $data['old'])
                : null,
        ];
    }

    private function phoneType(array $data): array
    {
        $types = config('app.phone_types');

        return [
            'field' => $data['field'],
            'new' => $types[$data['new']] ?? null,
            'old' => $types[$data['old']] ?? null,
        ];
    }

    private function messengerType(array $data): array
    {
        $items = Client\MessengerType::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function role(array $data): array
    {
        $items = Role::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function employee(array $data): array
    {
        $items = Employee::query()
            ->select(['name', 'l_name', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->keyBy('id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => isset($items[$data['new']])
                ? $items[$data['new']]['name'] .' '. $items[$data['new']]['l_name']
                : null,
            'old' => isset($items[$data['old']])
                ? $items[$data['old']]['name'] .' '. $items[$data['old']]['l_name']
                : null,
        ];
    }

    private function user(array $data): array
    {
        $items = User::query()
            ->select(['name', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->keyBy('id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => isset($items[$data['new']])
                ? $items[$data['new']]['name']
                : null,
            'old' => isset($items[$data['old']])
                ? $items[$data['old']]['name']
                : null,
        ];
    }

    private function client(array $data): array
    {
        $items = Client::query()
            ->select(['name', 'lname', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->keyBy('id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => isset($items[$data['new']])
                ? $items[$data['new']]['name'] . ' ' . $items[$data['new']]['lname']
                : null,
            'old' => isset($items[$data['old']])
                ? $items[$data['old']]['name'] . ' ' . $items[$data['old']]['lname']
                : null,
        ];
    }

    private function employees(array $data): array
    {
        $new = [];
        if($data['new']){
            $new = explode(',', trim($data['new'], ','));
        }
        $old = [];
        if($data['old']){
            $old = explode(',', trim($data['old'], ','));
        }

        $items = Employee::query()
            ->select(['name', 'l_name', 'id'])
            ->whereIn('id', array_merge($new, $old))
            ->get()
            ->keyBy('id')
            ->toArray()
        ;

        $newItems = null;
        $oldItems = null;
        foreach($new as $newId){
            $newItems .= $items[$newId]['name'] .' '. $items[$newId]['l_name'] . ', ';
        }
        foreach($old as $oldId){
            $oldItems .= $items[$oldId]['name'] .' '. $items[$oldId]['l_name'] . ', ';
        }

        if($newItems){
            $newItems = substr(trim($newItems), 0, -1);
        }
        if($oldItems){
            $oldItems = substr(trim($oldItems), 0, -1);
        }

        return [
            'field' => 'employee',
            'new' => $newItems,
            'old' => $oldItems,
        ];
    }

    private function material(array $data): array
    {
        $items = Material::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => 'material',
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function flights(array $data): array
    {
        return [
            'field' => $data['field'],
            'new' => FlightTypeEnum::getLabelAsNameByValue($data['new']),
            'old' => FlightTypeEnum::getLabelAsNameByValue($data['old']),
        ];
    }

    private function truck(array $data): array
    {
        $items = Truck::query()
            ->select(['title', 'id'])
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        return [
            'field' => $data['field'],
            'new' => $items[$data['new']] ?? null,
            'old' => $items[$data['old']] ?? null,
        ];
    }

    private function trucks(array $data): array
    {
        $new = [];
        if($data['new']){
            $new = explode(',', $data['new']);
        }
        $old = [];
        if($data['old']){
            $old = explode(',', $data['old']);
        }

        $items = Truck::query()
            ->select(['title', 'id'])
            ->whereIn('id', array_merge($new, $old))
            ->get()
            ->pluck('title', 'id')
            ->toArray()
        ;

        $newItems = null;
        $oldItems = null;
        foreach($new as $newId){
            $newItems .= $items[$newId] . ', ';
        }
        foreach($old as $oldId){
            $oldItems .= $items[$oldId] . ', ';
        }

        if($newItems){
            $newItems = substr(trim($newItems), 0, -1);
        }
        if($oldItems){
            $oldItems = substr(trim($oldItems), 0, -1);
        }

        return [
            'field' => 'truck',
            'new' => $newItems,
            'old' => $oldItems,
        ];
    }

    private function workDispatch(array $data): array
    {
        $items = WorkDispatchTouch::query()
            ->whereIn('id', [$data['new'], $data['old']])
            ->get()
            ->keyBy('id')
        ;

        return [
            'field' => 'work',
            'new' => isset($items[$data['new']])
                ? $items[$data['new']]->worksNameAsSrt()
                : null,
            'old' => isset($items[$data['old']])
                ? $items[$data['old']]->worksNameAsSrt()
                : null,
        ];
    }

    private function tags(array $data): array
    {
        return [
            'field' => $data['field'],
            'new' => $this->prettyTags($data['new']),
            'old' => $this->prettyTags($data['old']),
        ];
    }

    private function customTags(array $data): array
    {
        return [
            'field' => 'tags',
            'new' => !empty($data['new'])
                ? implode(', ', $data['new'])
                : null,
            'old' => !empty($data['old'])
                ? implode(', ', $data['old'])
                : null,
        ];
    }

    private function prettyTags($data): ?string
    {
        if(is_string($data)) {
            return $data;
        }
        if(is_array($data)) {
            $res = null;
            foreach ($data as $value) {
                $res .= $value['title'] . ', ';
            }
            $res = trim($res);

            if(substr($res, -1) == ',') {
                $res = substr($res, 0, -1);
            }

            if($res == ''){
                return null;
            }

            $data = $res;
        }

        return $data;
    }

}

