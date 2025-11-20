<?php

namespace App\Repositories;

use App\Helpers\ParseQueryParams;
use App\Models\Report\Location;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository
{
    public function query()
    {
        return Report::query();
    }
//
////    public function getReportByFeatureAndStatus($featureId, $status): Collection
////    {
////        return $this->query()
////            ->with(['features'])
////            ->whereHas('features', function ($q) use ($featureId){
////                $q->where('feature_id', $featureId);
////            })
////            ->where('status', $status)
////            ->get();
////    }
//
//    public function getById($id)
//    {
//        return $this->query()->where('id', $id)->first();
//    }
//
//    public function getForPush(
//        $sendPush = null,
//        $days = Report::DEFAULT_DAY_FOR_PUSH
//    )
//    {
//        $now = Carbon::now();
//        $future = Carbon::now()->addDays($days);
//
//        $query = $this->query()
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            });
//
//        if(null !== $sendPush){
//            $query->whereHas('pushData', function ($q) use ($sendPush) {
//                    $q->where('send_push', (bool)$sendPush);
//                });
//        }
//
//        return $query->get();
//    }
//
//    public function getPushForWeek($isSend = null, $days = 7, $now = null)
//    {
//        $now = $now ?? Carbon::now();
//        $future = Carbon::now()->addDays($days);
//
//        $query = $this->query()
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            });
//
//        if(null !== $isSend){
//            $query->whereHas('pushData', function ($q) use ($isSend) {
//                $q->where('is_send_week', (bool)$isSend);
//            });
//        }
//
//        return $query->get();
//    }
//
//    public function getPushStartDay($isSend = null, $hours = 39, $now = null)
//    {
//        $now = $now ??  Carbon::now();
//        $future = Carbon::now()->addHours($hours);
//
//        $query = $this->query()
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            });
//
//        if(null !== $isSend){
//            $query->whereHas('pushData', function ($q) use ($isSend) {
//                $q->where('is_send_start_day', (bool)$isSend);
//            });
//        }
//
//        return $query->get();
//    }
//
//
//    public function getPushEndDay($isSend = null, $hours = 30, $now = null)
//    {
//        $now = $now ??  Carbon::now();
//        $future = Carbon::now()->addHours($hours);
//
//        $query = $this->query()
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            });
//
//        if(null !== $isSend){
//            $query->whereHas('pushData', function ($q) use ($isSend) {
//                $q->where('is_send_end_day', (bool)$isSend);
//            });
//        }
//
//        return $query->get();
//    }
//
//    public function getForPushBetweenHour($start, $end, $days): Collection
//    {
//        $now = Carbon::now();
//        $future = Carbon::now()->addDays($days);
//
//        return $this->query()
//            ->with(['pushData'])
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            })
//            ->get()
//            ->filter(function($model) use ($start, $end, $now) {
//                $diff = $model->pushData->planned_at->diffInHours($now);
//                return $diff > $start && $diff < $end;
//            });
//    }
//
//    public function count()
//    {
//        return $this->query()->count();
//    }
//
//    public function getByIdWithRelations($id)
//    {
//        return $this->query()
//            ->with([
//                'user',
//                'user.profile',
//                'user.dealer',
//                'user.dealer.tm',
//                'clients',
//                'clients.region',
//                'reportClients',
//                'location',
//                'reportMachines',
//                'reportMachines.equipmentGroup',
//                'reportMachines.modelDescription',
//                'reportMachines.manufacturer',
//                'features.feature',
//                'features.value',
//            ])
//            ->where('id', $id)
//            ->first();
//    }
//
//    public function getByIdSomeRel($id, array $relation = [])
//    {
//        return $this->query()
//            ->with($relation)
//            ->where('id', $id)
//            ->first();
//    }
//
//    public function gerSingleAsArray($id)
//    {
//        $query = $this->query()
//            ->with([
//                'user',
//                'user.dealer',
//                'user.profile',
//                'location',
//                'clients',
//                'reportClients',
//                'reportMachines' => function ($query) {
//                    $query->selectRaw(
//                        'equipment_group_id, equipment_group as equipment_group_name, model_description as model_description_name, model_description_id, trailed_equipment_type, header_brand, header_model, serial_number_header, machine_serial_number'
//                    );
//                },
//                'reportMachines.equipmentGroup',
//                'reportMachines.modelDescription',
//                'comment',
//                'images'
//                ])
//
//            ->where('id', $id)
//            ->first()
//            ->toArray();
//
//        return $query;
//    }
//
    public function getForStatistic($dealerId, $egId, $mdId, $country, $year)
    {
        $query = $this->query()->with([
            'user',
            'user.dealer',
            'location',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'features.feature',
            'features.value',
        ])->whereIn('status' , ReportStatus::listForMachineStatistics());

        $this->filterYear($year, $query);
        $this->filterCountries($country, $query);
        $this->filterDealerIds($dealerId, $query);
        $this->filterEquipmentGroupId($egId, $query);
        $this->filterModelDescriptionIds($mdId, $query);

        return $query->get();
    }

    /**
     * @param User $user
     * @param $request
     * @param bool $forExcel
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
     * @throws \Exception
     */
//    public function getAll(User $user, $request, $forExcel = false, $notStatuses = [])
//    {
//        $perPage = $request['perPage'] ?? Report::DEFAULT_PER_PAGE;
//
//        $query = Report::query()
//            ->with([
//                'user',
//                'user.profile',
//                'user.dealer',
//                'user.dealer.tm',
//                'clients',
//                'clients.region',
//                'reportClients',
//                'location',
//                'reportMachines',
//                'reportMachines.equipmentGroup',
//                'reportMachines.modelDescription',
//                'reportMachines.manufacturer',
//                'features.feature',
//                'features.value',
//            ]);
//
//        if(!empty($notStatuses)){
//            $query->whereNotIn('status' ,$notStatuses);
//        }
//
//        if($user->isPS()){
//            $this->filterUserIdOrDealerId($user, $query);
//        }
//
//        if($user->isSM()){
//            $this->filterSmId($user->dealer->sm->id, $query);
//
//            // фильтр по ps
//            if(isset($request['ps_id']) && $this->checkField($request['ps_id'])){
//                $this->filterUserId($request['ps_id'], $query);
//            }
//        }
//
//        if($user->isTM() || $user->isTMD()){
//            $this->filterTmId($user->id, $query);
//            // фильтр по дилеру
//            if(isset($request['dealer_id']) && $this->checkField($request['dealer_id'])){
//                $this->filterDealerId($request['dealer_id'], $query);
//            }
//        }
//        // запрос для админа
//        if($user->isAdmin()){
//            // фильтр по ps
//            if(isset($request['ps_id']) && $this->checkField($request['ps_id'])){
//                $this->filterUserId($request['ps_id'], $query);
//            }
//            // фильтр по дилеру
//            if(isset($request['dealer_id']) && $this->checkField($request['dealer_id'])){
//                $this->filterDealerId($request['dealer_id'], $query);
//            }
//            // фильтр по tm
//            if(isset($request['tm_id']) && $this->checkField($request['tm_id'])){
//                $this->filterTmId($request['tm_id'], $query);
//            }
//        }
//
//
//        // фильтр по equipment_group
//        if(isset($request['equipment_group_id']) && $this->checkField($request['equipment_group_id'])){
//            $this->filterEquipmentGroupId($request['equipment_group_id'], $query);
//        }
//        // фильтр по model description
//        if(isset($request['model_description_id']) && $this->checkField($request['model_description_id'])){
//            $this->filterModelDescriptionId($request['model_description_id'], $query);
//        }
//        // фильтр по machine serial number
//        if(isset($request['machine_serial_number']) && $this->checkField($request['machine_serial_number'])){
//            $this->filterMachineSerialNumber($request['machine_serial_number'], $query);
//        }
//
//        // фильтр по стране
//        if(isset($request[Location::TYPE_COUNTRY_FILTER])){
//            $this->filterCountry($request[Location::TYPE_COUNTRY_FILTER], $query);
//        }
//        // фильтр по области
//        if(isset($request[Location::TYPE_REGION_FILTER])){
//            $region = $request[Location::TYPE_REGION_FILTER];
//            $query->whereHas('location', function ($q) use($region) {
//                $q->where('region', $region);
//            });
//        }
//        // фильтр по району
//        if(isset($request[Location::TYPE_DISTRICT_FILTER])){
//            $district = $request[Location::TYPE_DISTRICT_FILTER];
//            $query->whereHas('location', function ($q) use($district) {
//                $q->where('district', $district);
//            });
//        }
//
//        // фильтр по ModelDescription у клиента
//        if(isset($request['client_model_description_id']) && $this->checkField($request['client_model_description_id'])){
//            $modelDescriptionId = $request['client_model_description_id'];
//            $query->where(function(Builder $q) use($modelDescriptionId) {
//                $q->orWhereHas('clients', function (Builder $q) use($modelDescriptionId) {
//                    $q->where('model_description_id', $modelDescriptionId);
//                })->orWhereHas('reportClients', function (Builder $q) use($modelDescriptionId) {
//                    $q->where('model_description_id', $modelDescriptionId);
//                });
//            });
//        }
//
//        // фильтр по значениям таблицы
//        if(isset($request['feature_value_id']) && $this->checkField($request['feature_value_id'])){
//            $valueId = $request['feature_value_id'];
//            $query->whereHas('features.value', function (Builder $q) use ($valueId){
//                $q->where('value_id', $valueId);
//            });
//        }
//
//        // фильтр по году
//        if(isset($request['year']) && $this->checkField($request['year'])){
//            $this->filterYear($request['year'], $query);
//        }
//        // фильтр по дате создания
//        if(isset($request['created']) && $this->checkField($request['created'])){
//            $this->filterCreated($request['created'], $query);
//        }
//        // фильтр статусу
//        if(isset($request['status']) && $this->checkField($request['status'])){
//            $query->where('status', $request['status']);
//        }
//
//        // сортировка по году
//        if(isset($request['sort']) && $this->checkField($request['sort'])){
//            $params = ParseQueryParams::bySort($request['sort']);
//            $query->orderBy('created_at', $params['type']);
//        } else {
//            $query->orderBy('created_at', 'desc');
//        }
//
//        // если нужно для выгрузки в excel
//        if($forExcel){
//            return $query->get();
//        }
//
//        return $query->paginate($perPage);
//    }

//    public function getAllForSearch(User $user, $request)
//    {
//        $search = $request['search'];
//
//        $perPage = $request['perPage'] ?? Report::DEFAULT_PER_PAGE;
//
//        $query = $this->getReportQueryWithRelations();
//
//        if($user->isPS()){
//            $query->where(function(Builder $query) use ($user){
//                $query->where('user_id', $user->id)
//                    ->orWhereHas('user', function(Builder $query) use ($user){
//                        $query->where('dealer_id', $user->dealer->id);
//                    });
//            });
//        }
//
//        if($user->isSM()){
//            $query->where(function(Builder $query) use ($user){
//                $query->whereHas('user.dealer.sm', function(Builder $query) use($user) {
//                    $query->where('id', $user->id);
//                });
//            });
//        }
//
//        if($user->isTM() || $user->isTMD()){
//            $query->where(function(Builder $query) use ($user){
//                $query->whereHas('user.dealer.tm', function(Builder $query) use($user) {
//                    $query->where('id', $user->id);
//                });
//            });
//        }
//
//        $query->where(function(Builder $query) use($search) {
//            // поиск по DealerCompany
//            $query->whereHas('user.dealer', function(Builder $query) use($search) {
//                $query->where('name', 'like', $search.'%');
//            })
//                // поиск по Eq model
//                ->orWhereHas('reportMachines.equipmentGroup', function(Builder $query) use ($search){
//                    $query->where('name', 'like', $search.'%');
//                })
//                // поиск по SerialNumber
//                ->orWhereHas('reportMachines', function(Builder $query) use ($search){
//                    $query->where('machine_serial_number', 'like', $search.'%');
//                })
//                // поиск по Client
//                ->orWhereHas('clients', function(Builder $query) use ($search){
//                    $query->where('company_name', 'like', $search.'%');
//                })
//                // поиск по DemoDriverSurname
//                ->orWhereHas('user.profile', function (Builder $query) use ($search){
//                    $query->where('last_name', 'like', $search.'%');
//                })
//            ;
//        });
//
//        return $query->orderBy('id')->paginate($perPage);
//    }

//    private function getReportQueryWithRelations()
//    {
//        return Report::query()
//            ->with(['user', 'user.profile', 'user.dealer', 'user.dealer.tm', 'clients', 'reportClients', 'location', 'reportMachines']);
//    }
//
    private function filterCountries($country, Builder $query)
    {
        $countries = $this->parseDateForArray($country);
        return $query->whereHas('location', function (Builder $q) use($countries) {
            $q->whereIn('country', $countries);
        });
    }
//
//    private function filterCountry($country, Builder $query)
//    {
//        $country = $this->parseDateForArray($country);
//
//        return $query->whereHas('location', function (Builder $q) use($country) {
//            $q->whereIn('country', $country);
//        });
//    }
//
//    private function filterDealerId($dealerId, Builder $query)
//    {
//        return $query->whereHas('user',function(Builder $query) use($dealerId) {
//            $query->where('dealer_id', '=', $dealerId);
//        });
//    }
//
    private function filterDealerIds($dealerIds, Builder $query)
    {
        $ids = $this->parseDateForArray($dealerIds);

        return $query->whereHas('user',function(Builder $query) use($ids) {
            $query->whereIn('dealer_id', $ids);
        });
    }
//
//    private function filterOrDealerId($dealerId, Builder $query)
//    {
//        return $query->orWhereHas('user',function(Builder $query) use($dealerId) {
//            $query->where('dealer_id', '=', $dealerId);
//        });
//    }
//
//    private function filterTmId($tmId, Builder $query)
//    {
//        return $query->whereHas('user', function(Builder $query) use($tmId) {
//            $query->whereHas('dealer', function(Builder $query) use($tmId) {
//                $query->whereHas('tm', function(Builder $query) use($tmId) {
//                    $query->where('id', $tmId);
//                });
//            });
//        });
//    }
//
//    private function filterSmId($smId, Builder $query)
//    {
//        return $query->whereHas('user.dealer.sm', function(Builder $query) use($smId) {
//            $query->where('id', $smId);
//        });
//
//    }
//
    private function filterEquipmentGroupId($eqId, Builder $query)
    {
        return $query->whereHas('reportMachines',function(Builder $query) use($eqId) {
            $query->whereHas('equipmentGroup', function(Builder $query) use ($eqId){
                $query->where('id', '=', $eqId);
            });
        });
    }
//
//    private function filterModelDescriptionId($mdId, Builder $query)
//    {
//        return $query->whereHas('reportMachines',function(Builder $query) use($mdId) {
//            $query->whereHas('modelDescription', function(Builder $query) use ($mdId){
//                $query->where('id', '=', $mdId);
//            });
//        });
//    }
//
    private function filterModelDescriptionIds($mdIds, Builder $query)
    {
        $ids = $this->parseDateForArray($mdIds);

        return $query->whereHas('reportMachines',function(Builder $query) use($ids) {
            $query->whereHas('modelDescription', function(Builder $query) use ($ids){
                $query->whereIn('id', $ids);
            });
        });
    }
//
//    private function filterMachineSerialNumber($msn, Builder $query)
//    {
//        return $query->whereHas('reportMachines',function(Builder $query) use($msn) {
//            $query->where('machine_serial_number', 'like', $msn .'%');
//        });
//    }
//
//    private function filterUserId($userId, Builder $query)
//    {
//        return $query->where('user_id', '=', $userId);
//    }
//
//    private function filterUserIdOrDealerId($user, Builder $query)
//    {
//        return $query->where(function(Builder $query) use ($user){
//            $query->where('user_id', $user->id)
//                ->orWhereHas('user', function(Builder $query) use ($user){
//                    $query->where('dealer_id', $user->dealer->id);
//                });
//        });
//    }
//
    private function filterYear($year, Builder $query)
    {
        return $query->whereYear('created_at', $year);
    }

//    /**
//     * @param $date
//     * @param Builder $query
//     * @return $this
//     * @throws \Exception
//     */
//    private function filterCreated($date, Builder $query)
//    {
//        $dateParse = ParseQueryParams::date($date);
//
//        return $query->whereBetween('created_at', [$dateParse[0], $dateParse[1]]);
//    }
//
//    private function checkField($field): bool
//    {
//        return !empty($field) && $field != 'null';
//    }
//
    private function parseDateForArray(string $str): array
    {
        $temp = explode(',', $str);
        $new = array_map(function($item){
            return trim($item);
        }, $temp);

        return $new;
    }
}
