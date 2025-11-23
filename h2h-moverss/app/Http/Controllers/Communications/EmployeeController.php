<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communications\EmployeeStatusRequest;
use App\Http\Resources\Employees\EmployeeCommunicationResource;
use App\Models\Employee;
use App\Models\User\Role;
use App\Services\Employees\CommunicationStatusService;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function __construct()
    {}

    /**
     * test @see \Tests\Feature\Communications\Employees\IndexTest
     */
    public function index(EmployeeStatusRequest $request): JsonResponse
    {
        try {
            if(to_bool($request->reload_sip_status)){
                $service = resolve(CommunicationStatusService::class);
                $service->updateSipStatus();
            }

            $roleIds = Role::select(['id'])
                ->whereIn('title', [
                    Role::MANAGER,
                    Role::ADMIN,
                ])
                ->get()
                ->pluck('id')
                ->toArray()
            ;

            $recs = Employee::query()
                ->with(['user'])
                ->active()
                ->where(function ($query) {
                    $query->whereNotNull('ringostat_id')
                        ->orWhereHas('pbxdata', function ($query) {
                            $query->whereNotNull('pbx_ext');
                        });
                })
                ->whereHas('user.roles', function ($query) use ($roleIds) {
                    $query->whereIn('role_id', $roleIds);
                })
                ->orderBy('id')
                ->get();

            $meta = [];

            $oncall = $recs->filter(function (Employee $rec) {
                return $rec->ringostat_call_rec_id || $rec->zadarma_call_rec_id;
            })->sortBy('id');
            $meta['count_oncall'] = $oncall->count();
            $recs->forget($oncall->keys()->toArray());

            $online = $recs->filter(function (Employee $rec) {
                return $rec->isOnline();
            })->sortBy('id');
            $meta['count_online'] = $online->count();
            $recs->forget($online->keys()->toArray());

            $offline = $recs->filter(function (Employee $rec) {
                return !$rec->isOnline();
            })->sortBy('id');
            $meta['count_offline'] = $offline->count();
            $recs->forget($offline->keys()->toArray());

            $merge = $oncall->merge($online);

            if(
                $request->show_offline === false
                || $request->show_offline === "0"
                || $request->show_offline === "false"
            ){
                $meta['count_offline'] = 0;
            } else {
                $merge = $merge->merge($offline);
            }

        } catch (\Throwable $e) {
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'records' => EmployeeCommunicationResource::collection($merge),
            'meta' => $meta,
        ]);
    }
}

