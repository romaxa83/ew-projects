<?php
//
//namespace App\Services\Audit;
//
//use App\Models\Audit;
//use App\Models\BuildingType;
//use App\Models\Client;
//use App\Models\Division;
//use App\Models\Employee;
//use App\Models\MoveSize;
//use App\Models\Order\Material;
//use App\Models\Order\Notes;
//use App\Models\Order\Source;
//use App\Models\Order\Status;
//use App\Models\Order\WorkDispatchTouch;
//use App\Models\ParkingType;
//use App\Models\PaymentAccount;
//use App\Models\Settings\WaypointFlights;
//use App\Models\Truck\Truck;
//
//class NormalizeDetailsService
//{
//    private function changeNamingField(): array
//    {
//        return [
//            'lname' => 'last_name',
//            'division_id' => 'division',
//            'type_id' => 'type',
//        ];
//    }
//
//    // исключение полей для всех записей
//    private function excludeFields(): array
//    {
//        return [
//            'id',
//            'hash',
//            'sort'
//        ];
//    }
//
//    // исключение полей для конкретных сущностей
//    private function excludeFieldsByEntity(): array
//    {
//        return [
//            Client\Email::MORPH_NAME => [
//                'client_id'
//            ],
//            Client\Phone::MORPH_NAME => [
//                'client_id'
//            ],
//        ];
//    }
//
//    // исключение полей для конкретных сущностей, если запись удалены
//    private function excludeFieldsByEntityEventDeleted(): array
//    {
//        return [
//            Client\Phone::MORPH_NAME => [
//                'type_id',
//                'is_primary',
//                'client_id',
//            ],
//            Client\Email::MORPH_NAME => [
//                'type_id',
//                'is_primary',
//                'client_id',
//            ],
//        ];
//    }
//    private function boolFields(): array
//    {
//        return [
//            'is_auto',
//            'is_section',
//            'has_elevator',
//            'sizing_is_auto',
//            'in_dispatch',
//            'need_packing',
//            'need_unpacking',
//            'is_primary',
//        ];
//    }
//
//    /**
//     * todo дополнить тесты
//     * test @see \Tests\Unit\Services\Audit\NormalizeDetailService\OrderDetailTest
//     */
//    public function orderDetails(Audit $model, $data): array
//    {
//        foreach ($data as $key => $value) {
//            if(in_array($value['field'], $this->excludeFields())) {
//                unset($data[$key]);
//                continue;
//            }
//
////            if($value['event'] == Audit::EVENT_DELETED) {
////                if(key_exists($value['entity'], $this->excludeFieldsByEntityEventDeleted())) {
////                    if(in_array($value['field'], $this->excludeFieldsByEntityEventDeleted()[$value['entity']])) {
////                        unset($data[$key]);
////                        continue;
////                    }
////                }
////            } else {
////                if(key_exists($value['entity'], $this->excludeFieldsByEntity())) {
////                    if(in_array($value['field'], $this->excludeFieldsByEntity()[$value['entity']])) {
////                        unset($data[$key]);
////                        continue;
////                    }
////                }
////            }
//
//
//
//            if(key_exists($value['field'], $this->changeNamingField())) {
//                $data[$key]['field'] = $this->changeNamingField()[$value['field']];
//                $value['field'] = $this->changeNamingField()[$value['field']];
//            }
//            if(in_array($value['field'], $this->boolFields())) {
//                $data[$key] = $this->bool($value);
//            }
//
//            if($value['field'] == 'status_id'){
//                $data[$key] = $this->status($value);
//            }
//            if($value['field'] == 'division'){
//                $data[$key] = $this->division($value);
//            }
//            if($value['field'] == 'truck_id'){
//                $data[$key] = $this->truck($value);
//            }
//            if($value['field'] == 'truck_ids'){
//                $data[$key] = $this->trucks($value);
//            }
//            if($value['field'] == 'work_id'){
//                $data[$key] = $this->work($value);
//            }
//            if($value['field'] == 'source_id'){
//                $data[$key] = $this->source($value);
//            }
//            if($value['field'] == 'move_size_id'){
//                $data[$key] = $this->moveSize($value);
//            }
//            if($value['field'] == 'payment_account_id'){
//                $data[$key] = $this->paymentAccount($value);
//            }
//            if($value['field'] == 'tags'){
//                $data[$key] = $this->tags($value);
//            }
//            if($value['field'] == 'building_type_id'){
//                $data[$key] = $this->buildingType($value);
//            }
//            if($value['field'] == 'parking_type_id'){
//                $data[$key] = $this->parkingType($value);
//            }
//            if($value['field'] == 'flights_id'){
//                $data[$key] = $this->flights($value);
//            }
//            if($value['field'] == 'notes_by'){
//                $data[$key] = $this->notesBy($value);
//            }
//            if($value['field'] == 'material_id'){
//                $data[$key] = $this->material($value);
//            }
//            if($value['field'] == 'employer_id'){
//                $data[$key] = $this->employee($value);
//            }
//            if($value['field'] == 'employee_ids'){
//                $data[$key] = $this->employees($value);
//            }
////            if($value['field'] == 'type'){
////
////                if($value['entity'] == Client\Phone::MORPH_NAME){
////                    $data[$key] = $this->phoneType($value);
////                }
////
////            }
//
//            if($value['new'] === null && $value['old'] === null) {
//                unset($data[$key]);
//            }
//
//            //... division_id building_type_id  notes_by
//        }
//
//        // если лог о добавлен файл, оставляем только название файла
//        if($model->isAttachment()){
//            if($model->isEventDeleted()){
//                if(isset($model->old_values['miscs']) && !empty($model->old_values['miscs'])) {
//                    $tmp = json_decode($model->old_values['miscs'], true);
//                    if(isset($tmp['file']) && !empty($tmp['file'])) {
//                        $data = [];
//                        foreach ($tmp['file'] as $k => $v) {
//                            $data[] = [
//                                'field' => $k,
//                                'new' => null,
//                                'old' =>$v,
//                            ];
//                        }
//                    }
//                }
//            } else {
//                if(isset($model->new_values['miscs']) && !empty($model->new_values['miscs'])) {
//                    $tmp = json_decode($model->new_values['miscs'], true);
//                    if(isset($tmp['file']) && !empty($tmp['file'])) {
//                        $data = [];
//                        foreach ($tmp['file'] as $k => $v) {
//                            $data[] = [
//                                'field' => $k,
//                                'new' => $v,
//                                'old' => null,
//                            ];
//                        }
//                    }
//                }
//            }
//        }
//
//        return array_values($data);
//    }
//
//    private function bool(array $data): array
//    {
//        return [
//            'field' => $data['field'],
//            'new' => $this->boolCheck($data['new']),
//            'old' => $this->boolCheck($data['old']),
//        ];
//    }
//
//    private function boolCheck($value): ?string
//    {
//        if($value === 1){
//            return 'true';
//        }
//        if($value === 0){
//            return 'false';
//        }
//
//        return null;
//    }
//
//    private function status(array $data): array
//    {
//        $items = Status::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'status',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function source(array $data): array
//    {
//        $items = Source::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'source',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function moveSize(array $data): array
//    {
//        $items = MoveSize::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'move_size',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function paymentAccount(array $data): array
//    {
//        $items = PaymentAccount::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'payment_account',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function division(array $data): array
//    {
//        $items = Division::query()
//            ->select(['name', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('name', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => $data['field'],
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function buildingType(array $data): array
//    {
//        $items = BuildingType::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'building_type',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function parkingType(array $data): array
//    {
//        $items = ParkingType::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'parking_type',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function phoneType(array $data): array
//    {
//        $types = config('app.phone_types');
//
//        return [
//            'field' => $data['field'],
//            'new' => $types[$data['new']] ?? null,
//            'old' => $types[$data['old']] ?? null,
//        ];
//    }
//
//    private function employee(array $data): array
//    {
//        $items = Employee::query()
//            ->select(['name', 'l_name', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->keyBy('id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'employee',
//            'new' => isset($items[$data['new']])
//                ? $items[$data['new']]['name'] .' '. $items[$data['new']]['l_name']
//                : null,
//            'old' => isset($items[$data['old']])
//                ? $items[$data['old']]['name'] .' '. $items[$data['old']]['l_name']
//                : null,
//        ];
//    }
//
//    private function employees(array $data): array
//    {
//        $new = [];
//        if($data['new']){
//            $new = explode(',', trim($data['new'], ','));
//        }
//        $old = [];
//        if($data['old']){
//            $old = explode(',', trim($data['old'], ','));
//        }
//
//        $items = Employee::query()
//            ->select(['name', 'l_name', 'id'])
//            ->whereIn('id', array_merge($new, $old))
//            ->get()
//            ->keyBy('id')
//            ->toArray()
//        ;
//
//        $newItems = null;
//        $oldItems = null;
//        foreach($new as $newId){
//            $newItems .= $items[$newId]['name'] .' '. $items[$newId]['l_name'] . ', ';
//        }
//        foreach($old as $oldId){
//            $oldItems .= $items[$oldId]['name'] .' '. $items[$oldId]['l_name'] . ', ';
//        }
//
//        if($newItems){
//            $newItems = substr(trim($newItems), 0, -1);
//        }
//        if($oldItems){
//            $oldItems = substr(trim($oldItems), 0, -1);
//        }
//
//        return [
//            'field' => 'employee',
//            'new' => $newItems,
//            'old' => $oldItems,
//        ];
//    }
//
//    private function material(array $data): array
//    {
//        $items = Material::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'material',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function flights(array $data): array
//    {
//        $items = WaypointFlights::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'flights',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function truck(array $data): array
//    {
//        $items = Truck::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'truck',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function trucks(array $data): array
//    {
//        $new = [];
//        if($data['new']){
//            $new = explode(',', $data['new']);
//        }
//        $old = [];
//        if($data['old']){
//            $old = explode(',', $data['old']);
//        }
//
//        $items = Truck::query()
//            ->select(['title', 'id'])
//            ->whereIn('id', array_merge($new, $old))
//            ->get()
//            ->pluck('title', 'id')
//            ->toArray()
//        ;
//
//        $newItems = null;
//        $oldItems = null;
//        foreach($new as $newId){
//            $newItems .= $items[$newId] . ', ';
//        }
//        foreach($old as $oldId){
//            $oldItems .= $items[$oldId] . ', ';
//        }
//
//        if($newItems){
//            $newItems = substr(trim($newItems), 0, -1);
//        }
//        if($oldItems){
//            $oldItems = substr(trim($oldItems), 0, -1);
//        }
//
//        return [
//            'field' => 'truck',
//            'new' => $newItems,
//            'old' => $oldItems,
//        ];
//    }
//
//    private function work(array $data): array
//    {
//        $items = WorkDispatchTouch::query()
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->keyBy('id')
//        ;
//
//        return [
//            'field' => 'work',
//            'new' => isset($items[$data['new']])
//                ? $items[$data['new']]->worksNameAsSrt()
//                : null,
//            'old' => isset($items[$data['old']])
//                ? $items[$data['old']]->worksNameAsSrt()
//                : null,
//        ];
//    }
//
//    private function notesBy(array $data): array
//    {
//        $items = Notes::query()
//            ->select(['text', 'id'])
//            ->whereIn('id', [$data['new'], $data['old']])
//            ->get()
//            ->pluck('text', 'id')
//            ->toArray()
//        ;
//
//        return [
//            'field' => 'notes_by',
//            'new' => $items[$data['new']] ?? null,
//            'old' => $items[$data['old']] ?? null,
//        ];
//    }
//
//    private function tags(array $data): array
//    {
//        return [
//            'field' => 'tags',
//            'new' => $this->prettyTags($data['new']),
//            'old' => $this->prettyTags($data['old']),
//        ];
//    }
//
//    private function prettyTags($data): ?string
//    {
//        if(is_string($data)) {
//            return $data;
//        }
//        if(is_array($data)) {
//            $res = null;
//            foreach ($data as $value) {
//                $res .= $value['title'] . ', ';
//            }
//            $res = trim($res);
//
//            if(substr($res, -1) == ',') {
//                $res = substr($res, 0, -1);
//            }
//
//            if($res == ''){
//                return null;
//            }
//
//            $data = $res;
//        }
//
//        return $data;
//    }
//
//}
