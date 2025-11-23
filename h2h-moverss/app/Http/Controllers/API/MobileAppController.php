<?php

namespace App\Http\Controllers\API;

use App\Enums\Common\LogKeyEnum;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EstimateController;
use App\Http\Requests\Api\Order\MobileEstimateUpdateRequest;
use App\Http\Requests\Api\Order\NoteRequest;
use App\Http\Requests\Api\Order\RejectRequest;
use App\Http\Resources\Calculations\LocalHourlyRatesResource;
use App\Jobs\SendMobileAppDocumentToTelegram;
use App\Models\Attachment;
use App\Models\Calculation\LocalHourlyRates;
use App\Models\DispatchEmployer;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Order;
use App\Models\Order\MobileEstimate;
use App\Models\Order\Payment;
use App\Models\PaymentAccount;
use App\Models\PeakDate;
use App\Models\User\Role;
use App\Services\Communications\RecordCreateService;
use App\Services\Orders\OrderNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\{JsonResponse, Request};
use Auth, Exception, DB, Storage;

class MobileAppController extends Controller
{
    public function getOrders(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->load('employee');

        $employee_id = $user->employee->id ?? null;
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        \Log::info(LogKeyEnum::Api(). " GET Orders", [
            'filter' => $validated,
        ]);

        $orders = Order::query()
            ->with([
                'mobileEstimate:bol_signed_at,order_id',
                'status',
                'client:id,name,lname', 'client.phones:id,client_id,value', 'client.emails:id,client_id,value',
                'works.workTypes',
                'waypoints' => function ($q) {
                    $q->select(['order_id', 'type', 'state', 'zip', 'city', 'address', 'ap', 'sort']);
                },
            ])
            ->whereHas('works', function ($q) use ($validated) {
                return $q->where('start_date', $validated['date']);
            })
            ->whereHas('estimate', function ($q) use ($validated) {
                return $q->where('type', 'local');
            })
            ->join('orders_works', 'orders_works.order_id', '=', 'orders.id')
            ->join('dispatch_employees', 'dispatch_employees.work_id', '=', 'orders_works.id')
            ->where('dispatch_employees.employer_id', $employee_id)
//            ->orderBy('orders.id', 'desc')
            ->orderBy('orders_works.start_time', 'ASC')
            ->selectRaw('orders.*,orders_works.start_date as work_start_date')
            ->distinct()
            ->paginate()
        ;

        return response()
            ->json([
                'orders' => $orders,
            ]);
    }

    public function getOrder($order_id, Order $_order): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " GET Order #".$order_id);

        try {
            $this->hasPermissions($order_id);

            $order = $_order->query()
                ->withWaypointsFormat()
                ->with([
                    //'waypoints',
                    'works.dispatchEmployees.employee.rates',
                    'works.workTypes',
                    'payroll.items.employee:id,name,l_name',
                    'payroll.items.role:id,title',
                    'materials',
                    'customsExtras',
                    'mobileEstimate',
                    'estimate.local',
                    'client:id,name,lname',
                    'client.phones:id,client_id,value',
                    'client.emails:id,client_id,value',
                    'status',
                ])
                ->findOrFail($order_id);

            $order->load([
                'estimate.' . $order->estimate->type,
                'calculated' => function ($q) use ($order) {
                    $q->where('estimate_type', $order->estimate->type);
                }
            ]);

            $order->setRelation('calculated', EstimateController::getCalculatedSorted($order->calculated));

            $workRates = [];
            foreach ($order->works as $work) {

                $workClone = clone $work;
                $workClone->load([
                    'dispatchEmployees.employee.rates',
                    'dispatchEmployees.employee.user.roles'
                ]);

                // определяем текущий день, что прокинуть нужный рейт для сотрудника
                $workDate = $workClone->start_date;
                $typeDay = 3; // work_day
                $peakDays = PeakDate::query()
                    ->where('date', $workDate)
                    ->first();
                if($peakDays){
                    $typeDay = $peakDays->type_id;
                }

                $tmp = [];
                foreach ($workClone->dispatchEmployees as $dispatchEmployee) {

                    /** @var $dispatchEmployee DispatchEmployer */
                    /** @var $employee Employee */
                    $employee = $dispatchEmployee->employee;

                    $employeeRates = $employee
                        ->rates
                        ->where('division_id', $order->division_id)
                        ->where('season', $order->division->getNowSeason());

                    $rates = [];

                    foreach ($employeeRates as $employeeRate) {
                        /** @var $employeeRate Employee\Rate */
                        $rates[] = [
                            'role_id' => $employeeRate->role_id,
                            'role_name' => $employeeRate->role_name,
                            'season' => $employeeRate->season,
                            'type_day' => $typeDay,
                            'rate' => $employeeRate->getRateByDayType($typeDay),
                        ];
                    }

                    $roles = $employee->user->roles
                        ->where('for_crew', true)
                        ->map(fn (Role $item) => [
                            'id' => $item->id,
                            'title' => $item->title,
                        ])
                        ->toArray()
                    ;

                    $tmp[] = [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'l_name' => $employee->l_name,
                        'roles' => array_values($roles),
                        'rates' => $rates,
                    ];
                }

                $workRates[$work->id] = $tmp;
            }

            $rates = LocalHourlyRates::query()
                ->where('division_id', $order->division_id)
                ->where('season', $order->division->getNowSeason())
                ->get();

            if(
                $order->mobileEstimate &&
                is_null($order->mobileEstimate->waiver_client_name)
            ){
                $order->mobileEstimate->setWaiverClient(
                    $order->client?->full_name
                );
            }

        } catch (\Throwable $e) {
            \Log::error(LogKeyEnum::Api(). " GET Order #".$order_id . ' FAIL', [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'order' => $order,
                'works_data_for_payroll' => $workRates,
                'payments' => [
                    'accounts' => PaymentAccount::records()->where('division_id', $order->division_id)->get(),
                    'records' => Payment::where('order_id', $order_id)->with('account:id,title')->get()
                ],
                'signatures' => [
                    'estimate_signature_estimator' => Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_estimator.png'),
                    'estimate_signature_customer' => Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_customer.png'),
                    'bol_signature_customer_30cents' => Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer_30cents.png'),
                    'bol_signature_customer' => Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png'),
                    'bol_signature_shipper' => Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_shipper.png'),
                    MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT => Storage::disk('local')
                        ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT, $order_id)),
                    MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT => Storage::disk('local')
                        ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT, $order_id)),
                    MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY => Storage::disk('local')
                        ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY, $order_id)),
                    MobileEstimate::SIGNATURE_WAIVER_OVERSIZE => Storage::disk('local')
                        ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE, $order_id)),
                    MobileEstimate::SIGNATURE_WAIVER_CUSTOM => Storage::disk('local')
                        ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_CUSTOM, $order_id)),
                ],
                'extra' => Material::where('division_id', $order->division_id)
                    ->orderBy('sort')
                    ->get(['id', 'title', 'notes', 'price', 'need_packing', 'need_unpacking', 'packing_price', 'unpacking_price']),
                'rates' => LocalHourlyRatesResource::collection($rates),
            ]);
    }

    public function rejectOrder(RejectRequest $request, $id): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " REJECT Order #".$id, [
            'data' => $request->all(),
        ]);
        try {
            $data = $request->validated();

            $resp = [
                'success' => true,
                'msg' => "Order status changed to canceled.",
            ];

            $order = Order::query()
                ->with('statusHistory')
                ->where('id', $id)
                ->first();

            $newStatus = Order\Status::findOrFail(Order\Status::CANCELED_ID);

            if($order->status_id == Order\Status::CANCELED_ID){
                $resp['msg'] = "Order already canceled.";
                $resp['success'] = false;
            }

            make_transaction(function () use ($order, $newStatus, $data, &$resp){
                $order->statusHistory()
                    ->create([
                        'prev_status' => $order->status_id,
                        'new_status' => $newStatus->id,
                        'user_id' => Auth::id(),
                        'created_at' => now()->toDateTimeString()
                    ]);

                if ($newStatus->disable_dispatch) {
                    $resp['reload_works'] = (new Order\Work())->setAsCancellation($order->id, false);
                }

                $order->status_id = $newStatus->id;
                $order->reject_reason = $data['reject_reason'];
                $order->save();

                $note = new Order\Notes() ;
                $note->order_id = $order->id;
                $note->user_id = Auth::user()->id;
                $note->text = strip_tags($data['reject_reason'], '<br/>');
                $note->is_pinned = 0;
                $note->save();

                $note->audits()->update([
                    'is_show_to_log' => false
                ]);

                RecordCreateService::handler($note);
            });
        } catch (\Throwable $e) {
            \Log::error(LogKeyEnum::Api(). " REJECT Order #".$id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()->json($resp);
    }

    public function storeSignatures($order_id, Request $request): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " STORE Signatures for order #".$order_id, [
            'data' => $request->all(),
        ]);

        try {
            $serviceNotification = resolve(OrderNotificationService::class);

            $this->hasPermissions($order_id);

            $user = Auth::user()->load('employee');

            DB::beginTransaction();
            $request->validate([
                'estimate_signature_estimator' => 'file|max:' . (2 * 1024),
                'estimate_signature_customer' => 'file|max:' . (2 * 1024),
                'bol_signature_customer_30cents' => 'file|max:' . (2 * 1024),
                'bol_signature_customer' => 'file|max:' . (2 * 1024),
                'bol_signature_shipper' => 'file|max:' . (2 * 1024),
                MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT => 'file|max:' . (2 * 1024),
                MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT  => 'file|max:' . (2 * 1024),
                MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY  => 'file|max:' . (2 * 1024),
                MobileEstimate::SIGNATURE_WAIVER_OVERSIZE  => 'file|max:' . (2 * 1024),
                MobileEstimate::SIGNATURE_WAIVER_CUSTOM  => 'file|max:' . (2 * 1024),
            ]);

            $MobileEstimate = Order\MobileEstimate::where('order_id', $order_id)->firstOrFail();

            if ($request->has('estimate_signature_estimator'))
                $request->file('estimate_signature_estimator')
                    ->storeAs('signatures/' . $order_id . '/estimate/', 'signature_estimator.png');

            if ($request->has('estimate_signature_customer'))
                $request->file('estimate_signature_customer')
                    ->storeAs('signatures/' . $order_id . '/estimate/', 'signature_customer.png');

            if ($request->has(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT)){
                $request->file(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT)
                    ->storeAs(
                        MobileEstimate::getFileFolder(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT, $order_id),
                        MobileEstimate::getFileNameWithExt(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT)
                    )
                ;
                $MobileEstimate->update(['inspection_origin_signed_at' => CarbonImmutable::now()]);
            }

            if ($request->has(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT)) {
                $request->file(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT)
                    ->storeAs(
                        MobileEstimate::getFileFolder(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT, $order_id),
                        MobileEstimate::getFileNameWithExt(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT)
                    )
                ;
                $MobileEstimate->update(['inspection_destination_signed_at' => CarbonImmutable::now()]);
            }

            if ($request->has(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY)) {
                $request->file(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY)
                    ->storeAs(
                        MobileEstimate::getFileFolder(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY, $order_id),
                        MobileEstimate::getFileNameWithExt(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY)
                    )
                ;
                $MobileEstimate->update(['waiver_failure_to_protect_property_signed_at' => CarbonImmutable::now()]);
            }

            if ($request->has(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE)) {
                $request->file(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE)
                    ->storeAs(
                        MobileEstimate::getFileFolder(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE, $order_id),
                        MobileEstimate::getFileNameWithExt(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE)
                    )
                ;
                $MobileEstimate->update(['waiver_oversized_object_handling_signed_at' => CarbonImmutable::now()]);
            }

            if ($request->has(MobileEstimate::SIGNATURE_WAIVER_CUSTOM)) {
                $request->file(MobileEstimate::SIGNATURE_WAIVER_CUSTOM)
                    ->storeAs(
                        MobileEstimate::getFileFolder(MobileEstimate::SIGNATURE_WAIVER_CUSTOM, $order_id),
                        MobileEstimate::getFileNameWithExt(MobileEstimate::SIGNATURE_WAIVER_CUSTOM)
                    )
                ;
                $MobileEstimate->update(['waiver_custom_reason_signed_at' => CarbonImmutable::now()]);
            }

            if (
                (
                    $request->has('estimate_signature_estimator')
                    || $request->has('estimate_signature_customer')
                )
                && Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_estimator.png')
                && Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_customer.png')
            ) {
                $MobileEstimate->update([
                    'estimate_signed_at' => Carbon::now(),
                    'estimate_signed_employee_id' => $user?->employee?->id
                ]);
                $Attachment = $this->storeFileEstimatePdf($order_id, $MobileEstimate);
                SendMobileAppDocumentToTelegram::dispatch($Attachment->id, 'estimate');
                $serviceNotification->sendDocs($order_id, $Attachment, 'estimate');

                \Log::info(LogKeyEnum::Api(). " STORE Signatures for order #".$order_id, [
                    'estimate_signed' => true,
                ]);
            }

            if ($request->has('bol_signature_customer_30cents'))
                $request->file('bol_signature_customer_30cents')
                    ->storeAs('signatures/' . $order_id . '/bol/', 'signature_customer_30cents.png');

            if ($request->has('bol_signature_customer'))
                $request->file('bol_signature_customer')
                    ->storeAs('signatures/' . $order_id . '/bol/', 'signature_customer.png');
//            if ($request->has('bol_signature_shipper'))
//                $request->file('bol_signature_shipper')->storeAs('signatures/' . $order_id . '/bol/', 'signature_shipper.png');

            if (
                ($request->has('bol_signature_customer'))
                && Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png')
                && Storage::disk('local')->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT, $order_id))
                && Storage::disk('local')->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT, $order_id))
            ) {
                $MobileEstimate->update([
                    'bol_signed_at' => Carbon::now(),
                    'bol_signed_employee_id' => $user?->employee?->id
                ]);
                $Attachment = $this->storeFileBolPdf($order_id, $MobileEstimate);
                SendMobileAppDocumentToTelegram::dispatch($Attachment->id, 'bol');

                $attachmentInspectionPdf = $this->storeFileInspectionPdf($order_id, $MobileEstimate);
//                SendMobileAppDocumentToTelegram::dispatch($attachmentInspectionPdf->id, 'bol');

                $pdfAttachments[] = $attachmentInspectionPdf;

                if($this->hasWaiverSignature($order_id)){
                    $attachmentWavierPdf = $this->storeFileWaiverPdf($order_id, $MobileEstimate);
//                    SendMobileAppDocumentToTelegram::dispatch($attachmentWavierPdf->id, 'bol');
                    $pdfAttachments[] = $attachmentWavierPdf;
                }

                $serviceNotification->sendDocs($order_id, $Attachment, 'bol', $pdfAttachments);

                \Log::info(LogKeyEnum::Api(). " STORE Signatures for order #".$order_id, [
                    'bol_signed' => true,
                    'attachment_inspection_pdf' => $pdfAttachments,
                ]);
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            \Log::error(LogKeyEnum::Api(). " STORE Signatures for order #".$order_id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    protected function hasWaiverSignature($orderId): bool
    {
        return Storage::disk('local')->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY, $orderId))
            || Storage::disk('local')->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE, $orderId))
            || Storage::disk('local')->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_CUSTOM, $orderId))
            ;
    }

    public function deleteEstimate($order_id, Request $request)
    {
        \Log::info(LogKeyEnum::Api(). " DELETE Estimate for order #".$order_id, [
            'data' => $request->all(),
        ]);

        try {
            $this->hasPermissions($order_id);

            DB::transaction(function () use ($order_id, $request) {
                Order\MobileEstimate::where('order_id', $order_id)
                    ->firstOrFail()
                    ->update([
                        'estimate' => NULL,
                        'estimate_signed_at' => NULL,
                        'estimate_signed_employee_id' => null
                    ]);
                if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_customer.png')) {
                    Storage::disk('local')->delete('signatures/' . $order_id . '/estimate/signature_customer.png');
                }
                if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_estimator.png')) {
                    Storage::disk('local')->delete('signatures/' . $order_id . '/estimate/signature_estimator.png');
                }

            });
        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Api(). " DELETE Estimate for order #".$order_id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    public function storeNote(NoteRequest $request, $id)
    {
        \Log::info(LogKeyEnum::Api(). " STORE Foreman note for order #".$id, [
            'data' => $request->all(),
        ]);

        try {
            $this->hasPermissions($id);

            $user = Auth::user()->load('employee');

            $model = new Order\ForemanNote();
            $model->order_id = $id;
            $model->text = $request->text;
            $model->foreman_id = $user?->employee?->id;

            $model->save();
        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Api(). " STORE Foreman note for order #".$id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    /**
     * @throws \Throwable
     */
    public function deleteBol($order_id, Request $request): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " DELETE BOL for order #".$order_id, [
            'data' => $request->all(),
        ]);

        try {
            $this->hasPermissions($order_id);

            DB::transaction(function () use ($order_id, $request) {
                Order\MobileEstimate::where('order_id', $order_id)
                    ->firstOrFail()
                    ->update([
//                        'bol' => NULL,
                        'bol_signed_at' => NULL,
                        'bol_signed_employee_id' => null
                    ]);
                if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png')) {
                    Storage::disk('local')->delete('signatures/' . $order_id . '/bol/signature_customer.png');
                }
                if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_shipper.png')) {
                    Storage::disk('local')->delete('signatures/' . $order_id . '/bol/signature_shipper.png');
                }
                if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer_30cents.png')) {
                    Storage::disk('local')->delete('signatures/' . $order_id . '/bol/signature_customer_30cents.png');
                }

            });
        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Api(). " DELETE BOL for order #".$order_id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'success' => true,
            ]);

    }

    /**
     * @throws \Throwable
     */
    public function storeBol($order_id, Request $request): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " STORE BOL for order #".$order_id, [
            'data' => $request->all(),
        ]);

        try {
            $this->hasPermissions($order_id);

            DB::transaction(function () use ($order_id, $request) {
                $inputJSON = $request->getContent();
                $payload = json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR);
                Order\MobileEstimate::where('order_id', $order_id)->firstOrFail()->update(['bol' => $payload]);
            });
        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Api(). " STORE BOL for order #".$order_id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    /**
     * @throws \Throwable
     */
    public function storeEstimate($order_id, Request $request): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " STORE ESTIMATE for order #".$order_id, [
            'data' => $request->all(),
        ]);

        try {
            $this->hasPermissions($order_id);

            DB::transaction(function () use ($order_id, $request) {
                $inputJSON = $request->getContent();
                $payload = json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR);

                $model = Order\MobileEstimate::query()
                    ->updateOrCreate([
                        'order_id' => $order_id,
                    ], ['estimate' => $payload]);

                if(
                    is_null($model->waiver_client_name)
                ){
                    $order = Order::query()
                        ->with(['client'])
                        ->where('id', $order_id)
                        ->first();
                    $model->setWaiverClient(
                        $order->client?->full_name
                    );
                }
            });
        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Api(). " STORE ESTIMATE for order #".$order_id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    public function update(MobileEstimateUpdateRequest $request, $id): JsonResponse
    {
        \Log::info(LogKeyEnum::Api(). " Update for order #". $id, [
            'data' => $request->all(),
        ]);

        try {
            $this->hasPermissions($id);

            $model = Order\MobileEstimate::findOrFail($id);
            if($request->has('waiver_custom_reason')) {
                $model->waiver_custom_reason = $request->get('waiver_custom_reason');
            }

            $clientName = $model->waiver_client_name;
            if($request->has('waiver_property_client_name')) {
                $clientName['property_block'] = $request->get('waiver_property_client_name');
            }
            if($request->has('waiver_oversize_client_name')) {
                $clientName['oversize_block'] = $request->get('waiver_oversize_client_name');
            }
            if($request->has('waiver_custom_client_name')) {
                $clientName['custom_block'] = $request->get('waiver_custom_client_name');
            }
            $model->waiver_client_name = $clientName;

            $model->save();

        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Api(). " Update for order #".$id. " FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return response()->json([
            'success' => true,
        ]);
    }

    private function hasPermissions($order_id): void
    {
        $user = Auth::user();
        $user->load('employee');

        $employee_id = $user->employee->id ?? null;

        abort_if(!$employee_id, 403);

        // Check permissions
        $check = DispatchEmployer::query()
            ->where('employer_id', $employee_id)
            ->whereHas('work', function ($q) use ($order_id) {
                $q->where('order_id', $order_id);
            })
            ->with(['work'])
            ->first();

        abort_if(!$check, 403);
    }

    public function printBolPdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " PRINT BOL PDF for order #".$order_id);

        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();

        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png')) {
            $signature_customer = storage_path('app/signatures/' . $order_id . '/bol/signature_customer.png');
            $signature_customer_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_customer.png')));
        }
        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_shipper.png')) {
            $signature_shipper_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_shipper.png')));
            $signature_shipper = storage_path('app/signatures/' . $order_id . '/bol/signature_shipper.png');
        }

        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer_30cents.png')) {
            $signature_customer_30cents = storage_path('app/signatures/' . $order_id . '/bol/signature_customer_30cents.png');
            $signature_customer_30cents_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_customer_30cents.png')));
        }

        $pdf = Pdf::loadView('pdf.bol', [
            'data' => $mobileEstimate->bol,
            'model' => $mobileEstimate,
            'signature_customer' => $signature_customer ?? null,
            'signature_shipper' => $signature_shipper ?? null,
            'signature_customer_base64' => $signature_customer_base64 ?? null,
            'signature_shipper_base64' => $signature_shipper_base64 ?? null,
            'signature_customer_30cents' => $signature_customer_30cents ?? null,
            'signature_customer_30cents_base64' => $signature_customer_30cents_base64 ?? null,
        ]);

        return $pdf->stream();
    }

    public function viewBolPdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " VIEW BOL PDF for order #".$order_id);

        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();
        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png'))
            $signature_customer_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_customer.png')));
        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_shipper.png'))
            $signature_shipper_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_shipper.png')));
        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png'))
            $signature_customer = storage_path('app/signatures/' . $order_id . '/bol/signature_customer.png');
        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_shipper.png'))
            $signature_shipper = storage_path('app/signatures/' . $order_id . '/bol/signature_shipper.png');

        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer_30cents.png')) {
            $signature_customer_30cents = storage_path('app/signatures/' . $order_id . '/bol/signature_customer_30cents.png');
            $signature_customer_30cents_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_customer_30cents.png')));
        }

        return view('pdf.bol', [
            'data' => $mobileEstimate->bol,
            'model' => $mobileEstimate,
            'signature_customer' => $signature_customer ?? null,
            'signature_shipper' => $signature_shipper ?? null,
            'signature_customer_base64' => $signature_customer_base64 ?? null,
            'signature_shipper_base64' => $signature_shipper_base64 ?? null,
            'signature_customer_30cents' => $signature_customer_30cents ?? null,
            'signature_customer_30cents_base64' => $signature_customer_30cents_base64 ?? null,
        ]);
    }

    public function viewEstimatePdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " VIEW ESTIMATE PDF for order #".$order_id);

        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();
        if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_customer.png')) {
            $signature_customer = storage_path('app/signatures/' . $order_id . '/estimate/signature_customer.png');
            $signature_customer_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/estimate/signature_customer.png')));
        }
        if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_estimator.png')) {
            $signature_estimator = storage_path('app/signatures/' . $order_id . '/estimate/signature_estimator.png');
            $signature_estimator_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/estimate/signature_estimator.png')));
        }

        return view('pdf.estimate', [
            'data' => $mobileEstimate->estimate,
            'model' => $mobileEstimate,
            'signature_customer' => $signature_customer ?? null,
            'signature_estimator' => $signature_estimator ?? null,
            'signature_customer_base64' => $signature_customer_base64 ?? null,
            'signature_estimator_base64' => $signature_estimator_base64 ?? null,
        ]);
    }

    public function printEstimatePdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " PRINT ESTIMATE PDF for order #".$order_id);

        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();
        if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_customer.png')) {
            $signature_customer = storage_path('app/signatures/' . $order_id . '/estimate/signature_customer.png');
            $signature_customer_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/estimate/signature_customer.png')));
        }
        if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_estimator.png')) {
            $signature_estimator = storage_path('app/signatures/' . $order_id . '/estimate/signature_estimator.png');
            $signature_estimator_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/estimate/signature_estimator.png')));
        }

        $pdf = Pdf::loadView('pdf.estimate', [
            'data' => $mobileEstimate->estimate,
            'model' => $mobileEstimate,
            'signature_customer' => $signature_customer ?? null,
            'signature_estimator' => $signature_estimator ?? null,
            'signature_customer_base64' => $signature_customer_base64 ?? null,
            'signature_estimator_base64' => $signature_estimator_base64 ?? null,
        ]);

        return $pdf->stream();
//        return $pdf->download('invoice.pdf');
    }

    protected function storeFileBolPdf($order_id, MobileEstimate $mobileEstimate)
    {
        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();

        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer.png')) {
            $signature_customer = storage_path('app/signatures/' . $order_id . '/bol/signature_customer.png');
            $signature_customer_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_customer.png')));
        }
        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_shipper.png')) {
            $signature_shipper_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_shipper.png')));
            $signature_shipper = storage_path('app/signatures/' . $order_id . '/bol/signature_shipper.png');
        }

        if (Storage::disk('local')->has('signatures/' . $order_id . '/bol/signature_customer_30cents.png')) {
            $signature_customer_30cents = storage_path('app/signatures/' . $order_id . '/bol/signature_customer_30cents.png');
            $signature_customer_30cents_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/bol/signature_customer_30cents.png')));
        }

        $pdf = Pdf::loadView('pdf.bol', [
            'data' => $mobileEstimate->bol,
            'model' => $mobileEstimate,
            'signature_customer' => $signature_customer ?? null,
            'signature_shipper' => $signature_shipper ?? null,
            'signature_customer_base64' => $signature_customer_base64 ?? null,
            'signature_shipper_base64' => $signature_shipper_base64 ?? null,
            'signature_customer_30cents' => $signature_customer_30cents ?? null,
            'signature_customer_30cents_base64' => $signature_customer_30cents_base64 ?? null,
        ]);

        $folder = "attachments/order/{$order_id}/";
        $filename = "bol_" . now()->format('Y-m-d_H-i-s') . ".pdf";
        $pdf->save($folder . $filename, 'local');
        $hash = hash_file('sha256', Storage::disk('local')->path($folder . $filename));
        Storage::disk('local')->move($folder . $filename, $folder . $hash);

        $miscs = [
            'patch' => $folder,
            'size' => (new AttachmentController())->getHumanReadableFilesize(Storage::disk('local')->size($folder . $hash)),
            'name' => $filename,
        ];

        return Attachment::create([
            'user_id' => Auth::id(),
            'hash' => $hash,
            'description' => 'BOL from mobile app',
            'miscs' => [
                'object' => [
                    'type' => 'order',
                    'id' => (int)$order_id,
                ],
                'file' => $miscs,
            ]
        ]);
    }

    protected function storeFileEstimatePdf($order_id, MobileEstimate $mobileEstimate)
    {
        if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_customer.png')) {
            $signature_customer = storage_path('app/signatures/' . $order_id . '/estimate/signature_customer.png');
            $signature_customer_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/estimate/signature_customer.png')));
        }
        if (Storage::disk('local')->has('signatures/' . $order_id . '/estimate/signature_estimator.png')) {
            $signature_estimator = storage_path('app/signatures/' . $order_id . '/estimate/signature_estimator.png');
            $signature_estimator_base64 = base64_encode(file_get_contents(storage_path('app/signatures/' . $order_id . '/estimate/signature_estimator.png')));
        }

        $pdf = Pdf::loadView('pdf.estimate', [
            'data' => $mobileEstimate->estimate,
            'model' => $mobileEstimate,
            'signature_customer' => $signature_customer ?? null,
            'signature_estimator' => $signature_estimator ?? null,
            'signature_customer_base64' => $signature_customer_base64 ?? null,
            'signature_estimator_base64' => $signature_estimator_base64 ?? null,
        ]);

        $folder = "attachments/order/{$order_id}/";
        $filename = "estimate-" . now()->format('Y-m-d_H-i-s') . ".pdf";
        $pdf->save($folder . $filename, 'local');
        $hash = hash_file('sha256', Storage::disk('local')->path($folder . $filename));
        Storage::disk('local')->move($folder . $filename, $folder . $hash);

        $miscs = [
            'patch' => $folder,
            'size' => (new AttachmentController())->getHumanReadableFilesize(Storage::disk('local')->size($folder . $hash)),
            'name' => $filename,
        ];

        return Attachment::create([
            'user_id' => Auth::id(),
            'hash' => $hash,
            'description' => 'Estimate from mobile app',
            'miscs' => [
                'object' => [
                    'type' => 'order',
                    'id' => (int)$order_id,
                ],
                'file' => $miscs,
            ]
        ]);
    }

    public function viewInspectionPdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " VIEW INSPECTION PDF for order #".$order_id);

        return view('pdf.inspection', $this->getPdfDataForInspection($order_id, $mobileEstimate));
    }

    public function printInspectionPdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " PRINT INSPECTION PDF for order #".$order_id);

        $pdf = Pdf::loadView('pdf.inspection', $this->getPdfDataForInspection($order_id, $mobileEstimate));

        return $pdf->stream();
    }

    protected function storeFileInspectionPdf($order_id, MobileEstimate $mobileEstimate)
    {
        $pdfData = $this->getPdfDataForInspection($order_id, $mobileEstimate);

        $pdf = Pdf::loadView('pdf.inspection', $pdfData);

        $folder = "attachments/order/{$order_id}/";
        $filename = "inspection-" . now()->format('Y-m-d_H-i-s') . ".pdf";
        $pdf->save($folder . $filename, 'local');
        $hash = hash_file('sha256', Storage::disk('local')->path($folder . $filename));
        Storage::disk('local')->move($folder . $filename, $folder . $hash);

        $miscs = [
            'patch' => $folder,
            'size' => (new AttachmentController())->getHumanReadableFilesize(Storage::disk('local')->size($folder . $hash)),
            'name' => $filename,
        ];

        return Attachment::create([
            'user_id' => Auth::id(),
            'hash' => $hash,
            'description' => 'Inspection from mobile app',
            'miscs' => [
                'object' => [
                    'type' => 'order',
                    'id' => (int)$order_id,
                ],
                'file' => $miscs,
            ]
        ]);
    }

    protected function getPdfDataForInspection($order_id, MobileEstimate $mobileEstimate): array
    {
        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();
        if (
            Storage::disk('local')
                ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT, $order_id))
        ) {
            $signatureOriginAt = storage_path('app/'. MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_ORIGIN_AT, $order_id));
            $signatureOriginAtBase64 = base64_encode(file_get_contents($signatureOriginAt));
        }

        if (
            Storage::disk('local')
                ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT, $order_id))
        ) {
            $signatureDestinationAt = storage_path('app/'. MobileEstimate::getPath(MobileEstimate::SIGNATURE_INSPECTION_DESTINATION_AT, $order_id));
            $signatureDestinationAtBase64 = base64_encode(file_get_contents($signatureDestinationAt));
        }

        $order = Order::query()->with(['client'])
            ->where('id', $order_id)
            ->firstOrFail();

        return [
            'model' => $mobileEstimate,
            'client_name' => $order->client?->full_name ?? "",
            'signature_origin_at' => $signatureOriginAt ?? null,
            'signature_origin_at_base_64' => $signatureOriginAtBase64 ?? null,
            'signature_destination_at' => $signatureDestinationAt ?? null,
            'signature_destination_at_base_64' => $signatureDestinationAtBase64 ?? null,
        ];
    }

    public function viewWaiverPdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " VIEW WAIVER PDF for order #".$order_id);

        return view('pdf.waiver', $this->getPdfDataForWaiver($order_id, $mobileEstimate));
    }

    public function printWaiverPdf($order_id, MobileEstimate $mobileEstimate)
    {
        \Log::info(LogKeyEnum::Api(). " PRINT WAIVER PDF for order #".$order_id);

        $pdf = Pdf::loadView('pdf.waiver', $this->getPdfDataForWaiver($order_id, $mobileEstimate));

        return $pdf->stream();
    }

    protected function storeFileWaiverPdf($order_id, MobileEstimate $mobileEstimate)
    {
        $pdfData = $this->getPdfDataForWaiver($order_id, $mobileEstimate);

        $pdf = Pdf::loadView('pdf.waiver', $pdfData);

        $folder = "attachments/order/{$order_id}/";
        $filename = "waiver-" . now()->format('Y-m-d_H-i-s') . ".pdf";
        $pdf->save($folder . $filename, 'local');
        $hash = hash_file('sha256', Storage::disk('local')->path($folder . $filename));
        Storage::disk('local')->move($folder . $filename, $folder . $hash);

        $miscs = [
            'patch' => $folder,
            'size' => (new AttachmentController())->getHumanReadableFilesize(Storage::disk('local')->size($folder . $hash)),
            'name' => $filename,
        ];

        return Attachment::create([
            'user_id' => Auth::id(),
            'hash' => $hash,
            'description' => 'Waiver from mobile app',
            'miscs' => [
                'object' => [
                    'type' => 'order',
                    'id' => (int)$order_id,
                ],
                'file' => $miscs,
            ]
        ]);
    }

    protected function getPdfDataForWaiver($order_id, MobileEstimate $mobileEstimate): array
    {
        $mobileEstimate = $mobileEstimate->where('order_id', $order_id)->firstOrFail();
        if (
            Storage::disk('local')
                ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY, $order_id))
        ) {
            $signatureProtectedProperty = storage_path('app/'. MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_PROTECT_PROPERTY, $order_id));
            $signatureProtectedPropertyBase64 = base64_encode(file_get_contents($signatureProtectedProperty));
        }

        if (
            Storage::disk('local')
                ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE, $order_id))
        ) {
            $signatureOversize = storage_path('app/'. MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_OVERSIZE, $order_id));
            $signatureOversizeBase64 = base64_encode(file_get_contents($signatureOversize));
        }

        if (
            Storage::disk('local')
                ->has(MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_CUSTOM, $order_id))
        ) {
            $signatureCustom = storage_path('app/'. MobileEstimate::getPath(MobileEstimate::SIGNATURE_WAIVER_CUSTOM, $order_id));
            $signatureCustomBase64 = base64_encode(file_get_contents($signatureCustom));
        }

        $order = Order::query()->with(['client'])
            ->where('id', $order_id)
            ->firstOrFail();

        return [
            'model' => $mobileEstimate,
            'client_name' => $order->client?->full_name ?? "",
            'client_name_property_block' => $mobileEstimate->waiver_client_name['property_block'] ?? "",
            'client_name_oversize_block' => $mobileEstimate->waiver_client_name['oversize_block'] ?? "",
            'client_name_custom_block' => $mobileEstimate->waiver_client_name['custom_block'] ?? "",
            'signature_protect_property' => $signatureProtectedProperty ?? null,
            'signature_protect_property_base_64' => $signatureProtectedPropertyBase64 ?? null,
            'signature_oversized' => $signatureOversize ?? null,
            'signature_oversized_base_64' => $signatureOversizeBase64 ?? null,
            'signature_custom' => $signatureCustom ?? null,
            'signature_custom_base_64' => $signatureCustomBase64 ?? null,
        ];
    }
}
