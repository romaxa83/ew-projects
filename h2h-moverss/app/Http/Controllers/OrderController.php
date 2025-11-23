<?php

namespace App\Http\Controllers;

use App\Enums\Catalog\MoveSizeTypeEnum;
use App\Exceptions\Handler;
use App\Exports\OrdersExport;
use App\Http\Controllers\Mailbox\Gmail\GMailController;
use App\Http\Controllers\Zadarma\PBXController;
use App\Http\Requests\Order\{Ajax\LogRequest, ChangeClientRequest, CreateOrderRequest, OrderRequest};
use App\Services\Communications\RecordCreateService;
use App\Services\Communications\RecordUpdateService;
use App\Services\Payrolls\PayrollService;
use App\Models\{BuildingType,
    Client,
    Communications\ConversationMark,
    Division,
    Mailbox\Gmail\Account,
    Mailbox\Gmail\Message,
    MoveSize,
    Order,
    Order\Source,
    ParkingType,
    Ringostat\EventAfterCall,
    Settings\EmailTemplateGroup,
    Settings\EstimateParameters,
    Settings\OrderClosingStatus,
    Settings\WaypointFlights,
    Tasks\Task,
    Twilio\TwilioSms,
    WorkTypes,
    Zadarma\SmsEvents};
use App\Services\Audit\AuditFetchService;
use App\Traits\ResponseFormatter;
use App\User;
use App\Utils\FlashMessagesTrait;
use Arr;
use Auth;
use Carbon;
use DB;
use Excel;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Str;

/**
 * Main Order Controller.
 */
class OrderController extends Controller
{
    use ResponseFormatter, FlashMessagesTrait;

    /**
     * Order Model.
     * @var Order
     */
    private Order $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get all order statuses.
     * @return Collection
     */
    public function getStatuses(): Collection
    {
        return Order\Status::selected()
            ->get(['id', 'group_id', 'title'])
            ->sortBy('group.sort');
    }


    private function updateCommunicationStartDate($Object, $Date)
    {
        if ($Object->datetime > $Date) {
            $Date = $Object->datetime;
        }
        return $Date;
    }

    public function savePreset(Request $request)
    {
        try {
            $response = ['success' => false];
            $validated = $request->validate([
                'orderID' => 'required|integer|exists:orders,id',
                'presets' => 'array'
            ]);
            $presets = $request->session()->get('presets');
            if (empty($presets)) {
                $presets = [];
            }
            foreach ($validated['presets'] as $k => $v) {
                $presets[$validated['orderID']][$k] = $v;
            }
            $request->session()->put(['presets' => $presets]);
            $response = ['success' => true];
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }
        return response()
            ->json($response);
    }

    public function ajaxOrderPanelCommunications(Request $request)
    {
        try {
            $limit = 20;
            $records = collect([]);
            $response = [
                'success' => false
            ];
            $validated = $request->validate([
                'orderID' => 'required|integer|exists:orders,id',
                'historyTill' => 'nullable|date_format:U'
            ]);

            $PBXController = new PBXController();
            $PBXController->hasZadarma();
            $Order = Order::with([
                'client.phones', 'client.emails', 'pinnedNotes', 'pinnedNotes.author:id,name'
            ])->findOrFail($validated['orderID']);
            $DateFrom = $DateFromInitial = (new Carbon\Carbon($Order->created_at,
                config('app.timezone')))->modify("-2 day midnight")->setTimezone('UTC');
            if ($validated['historyTill']) {
                $DateTo = Carbon\Carbon::createFromTimestamp($validated['historyTill'], 'UTC');
            } else {
                $DateTo = Carbon\Carbon::now('UTC')->addDay();
                // Заказ со статусом закрыто
                if (in_array($Order->status_id, [9, 10, 12, 13, 14, 19])) {
                    $DateTo = $Order->updated_at->modify('+2 days midnight');
                }
            }


//            dd($DateFrom, $DateTo);

            // Notes
            $Order->load([
                'notes' => function (HasMany $q) use ($DateFrom, $DateTo, $limit) {
                    return $q->whereBetween('created_at', [$DateFrom, $DateTo])
                        ->with('author:id,name', 'author.employee:id,name,l_name')
                        ->orderBy('created_at', 'DESC')
                        ->limit($limit);
                }
            ]);
            if ($Order->notes->isNotEmpty()) {
                foreach ($Order->notes as $record) {
                    $records->push(
                        $this->getCommunicationPanelFormat($record, $Order)
                    );
                }
                if ($Order->notes->count() == $limit) {
                    $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                }
            }
            // Zadarma calls
            if ($Order->client_id && $Order->client->phones->count() > 0) {
                $phonesArray = $Order->client->phones->pluck('value')->toArray();
                if ($PBXController->getPBXid()) {
                    $zadarmaRecords = $PBXController->getZadarmaPhoneLog($Order, $phonesArray,
                        ['dateFrom' => $DateFrom, 'dateTo' => $DateTo, 'limit' => $limit]);


//                    dd($zadarmaRecords);
                    if ($zadarmaRecords->isNotEmpty()) {
                        $TimeZone = config('app.timezone');
                        if ($Order->division_id) {
                            $Division = Division::findOrFail($Order->division_id);
                            if (!empty($Division->miscs['tz'])) {
                                $TimeZone = $Division->miscs['tz'];
                            }
                        }
//                        dd($TimeZone);
                        foreach ($zadarmaRecords as $record) {
                            $records->push(
                                $this->getCommunicationPanelFormat($record, $TimeZone)
                            );
                        }
                        if ($zadarmaRecords->count() == $limit) {
                            $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                        }
                    }
                }

                $phones = $phonesArray;
                if ($PBXController->getPBXid()) {
                    $smsRecords = SmsEvents::where(function (Builder $q) use ($phones) {
                        $q->where(function (Builder $query) use ($phones) {
                            return $query->where('inbound', '=', 1)
                                ->where(function ($query) use ($phones) {
//                                if (!empty($phones)) {
                                    foreach ($phones as $phone) {
                                        $query->orWhere('caller_id', 'LIKE', '%' . $phone);
                                    }
//                                }
                                });

                        })->orWhere(function ($query) use ($phones) {
                            return $query->where('inbound', '=', 0)
                                ->where(function ($query) use ($phones) {
//                                if (!empty($phones)) {
                                    foreach ($phones as $phone) {
                                        $query->orWhere('caller_did', 'LIKE', '%' . $phone);
                                    }
//                                }
                                });
                        });
                    })->where('pbx_id', $PBXController->getPBXid())
                        ->whereBetween('created_at',
                            [$DateFrom, $DateTo])
                        ->orderBy('created_at', 'DESC')
                        ->limit($limit)
                        ->get();
                    if ($smsRecords->isNotEmpty()) {
                        foreach ($smsRecords as $record) {
                            $records->push(
                                $this->getCommunicationPanelFormat($record)
                            );
                        }
                        if ($smsRecords->count() == $limit) {
                            $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                        }

                    }
                }

                // Twilio
                $TwilioSmsRecords = TwilioSms::where(function (Builder $q) use ($phones) {
                    $q->where(function (Builder $query) use ($phones) {
                        return $query->where('direction', '=', 'inbound')
                            ->where(function ($query) use ($phones) {
                                foreach ($phones as $phone) {
                                    $query->orWhere('from', 'LIKE', '%' . $phone);
                                }
                            });

                    })->orWhere(function ($query) use ($phones) {
                        return $query->whereIn('direction', ['outbound-api'])
                            ->where(function ($query) use ($phones) {
                                if (!empty($phones)) {
                                    foreach ($phones as $phone) {
                                        $query->orWhere('to', 'LIKE', '%' . $phone);
                                    }
                                }
                            });
                    });
                })->whereBetween('created_at', [$DateFrom, $DateTo])
                    ->where('division', session('division')['id'])
                    ->orderBy('created_at', 'DESC')
                    ->with(['statuses' => function ($q) {
                        $q->orderBy('id', 'ASC');
                    }])
                    ->limit($limit)
                    ->get();
                if ($TwilioSmsRecords->isNotEmpty()) {
                    foreach ($TwilioSmsRecords as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                    if ($TwilioSmsRecords->count() == $limit) {
                        $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                    }

                }

                // Ringostat
                $ringostatProjectID = null;
                if ($Order->division_id) {
                    $Division = Division::findOrFail($Order->division_id);
                    if (!empty($Division->miscs['ringostat_project_id'])) {
                        $ringostatProjectID = $Division->miscs['ringostat_project_id'];
                    }
                }
                $RingostatRecords = EventAfterCall::where(function (Builder $q) use ($phones) {
                    $q->where(function (Builder $query) use ($phones) {
                        return $query->where('type', '=', 'in')
                            ->where(function ($query) use ($phones) {
                                foreach ($phones as $phone) {
                                    $query->orWhere('caller_number', 'LIKE', '%' . $phone);
                                }
                            });

                    })->orWhere(function ($query) use ($phones) {
                        return $query->where('type', '=', 'out')
                            ->where(function ($query) use ($phones) {
                                if (!empty($phones)) {
                                    foreach ($phones as $phone) {
                                        $query->orWhere('destination', 'LIKE', '%' . $phone);
                                    }
                                }
                            });
                    });
                })->where('call_timestamp', '>', $DateFrom->getPreciseTimestamp())
                    ->where('call_timestamp', '<' , $DateTo->getPreciseTimestamp())
                    ->where('project_id', '=' , $ringostatProjectID)
                    ->orderBy('call_timestamp', 'DESC')
                    ->limit($limit)
                    ->get();
                if ($RingostatRecords->isNotEmpty()) {
                    foreach ($RingostatRecords as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                    if ($RingostatRecords->count() == $limit) {
                        $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                    }

                }
            }

            // Future Tasks
            if (!$validated['historyTill']) {
                $FutureTasks = Task::byOrder($Order->id)
                    ->where('created_at', '>', $DateTo)
                    ->with('type', 'author:id,name', 'author.employee:id,name,l_name', 'executor:id,name',
                        'executor.employee:id,name,l_name')
                    ->orderBy('due_date', 'desc')->get();
                if ($FutureTasks->isNotEmpty()) {
                    foreach ($FutureTasks as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                }
            }

            // Tasks
            $Tasks = Task::byOrder($Order->id)
                ->whereBetween('created_at', [$DateFrom, $DateTo])
                ->with('type', 'author:id,name', 'author.employee:id,name,l_name', 'executor:id,name',
                    'executor.employee:id,name,l_name')
                ->limit($limit)->orderBy('due_date', 'desc')->get();
            if ($Tasks->isNotEmpty()) {
                foreach ($Tasks as $record) {
                    $this->getCommunicationPanelFormat($record);
                    $records->push(
                        $this->getCommunicationPanelFormat($record)
                    );
//                    $records[] = [
//                        'type' => 'zadarmaEvent',
//                        'dt' => (new Carbon\Carbon($record->call_start, $TimeZone))->setTimezone('UTC'),
//                        'data' => $record
//                    ];
                }
                if ($Tasks->count() == $limit) {
                    $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                }

            }
            // Activity
            $Activities = Order\Activity::whereOrderId($Order->id)
                ->with('author:id,name', 'author.employee:id,name,l_name')
                ->whereIn('type', ['status', 'user', 'division', 'source', 'email'])
//                ->where('type', '123')
                ->whereBetween('updated_at', [$DateFrom, $DateTo])
                ->orderBy('updated_at', 'DESC')
                ->limit($limit)
                ->get();
            if ($Activities->isNotEmpty()) {
                foreach ($Activities as $record) {
                    $records->push($this->getCommunicationPanelFormat($record));
                }
                if ($Activities->count() == $limit) {
                    $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                }

            }
            // Emails
            if ($Order->client_id && $Order->client->emails && $Order->client->emails->isNotEmpty()) {
                $divisionMailboxes = Account::where('division_id', session('division')['id'])->where('active', 1)->get('id');
                $GmailMessages = Message::searchByEmails($Order->client->emails->pluck('value')->toArray())
                    ->with(['data', 'account:id,miscs'])
                    // current division mailboxes
                    ->when($divisionMailboxes->isNotEmpty(), function ($q) use ($divisionMailboxes) {
                        $q->whereIn('account_id', $divisionMailboxes->pluck('id')->toArray());
                    })
                    ->when($divisionMailboxes->isEmpty(), function ($q) {
                        $q->where('account_id', 0);
                    })
                    ->whereBetween('created_at', [$DateFrom, $DateTo])
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
                if ($GmailMessages->isNotEmpty()) {
                    foreach ($GmailMessages as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                    if ($GmailMessages->count() == $limit) {
                        $DateFrom = $this->updateCommunicationStartDate($records->last(), $DateFrom);
                    }

                }
            }

            if ($Order->client_id) {
                // conversationMarks
                $ConversationMarks = ConversationMark::where('client_id', $Order->client_id)
                    ->whereBetween('created_at', [$DateFrom, $DateTo])
                    ->with('user', 'user.employee')
                    ->where('type', 'read')
                    ->limit($limit)
                    ->get();
                if ($ConversationMarks->isNotEmpty()) {
                    foreach ($ConversationMarks as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                }
            }

            // удаляем все что раньше $DateFrom
            if ($DateFrom > $DateFromInitial) {
                $records = $records->filter(function ($v, $k) use ($DateFrom) {
                    return $v->datetime >= $DateFrom;
                });
            }

            // если есть в будущем ставим разделитель

            $records = $records
                ->map(function ($v, $k) {
//                $v->timestamp = $v->datetime->getPreciseTimestamp(3);
                    $v->timestamp = $v->datetime->getTimestamp();
                    return $v;
                })->sortBy(function ($obj, $key) {
                    return $obj->datetime;
                });

            $response['data'] = [
                'pinnedNotes' => $Order->pinnedNotes->map((function ($v) {
                    return $this->getCommunicationPanelFormat($v);
                })),
                'records' => array_values($records->toArray()),
                'recordsTill' => $DateFrom->getTimestamp(),
                'dateFrom' => $DateFrom,
                'dateTill' => $DateTo,
//                'recordsDateTo' => $DateTo->format('Y-m-d H:i:s'),
                'more' => $records->count() >= $limit
            ];
            $response['success'] = true;
        } catch (Exception $e) {
            dump($e);
            $response['msg'] = $e->getMessage();
        }
        // callsRingCentral?
        // orderStatusChanges
        // Notes
        // Tasks


        return response()
            ->json($response);
    }


    /**
     * Get for the order: activities and gmail messages.
     * @param Request $request
     * @param Order\Activity $activity
     * @return JsonResponse
     */
    public function ajaxActivityCommunications(Request $request, Order\Activity $activity): JsonResponse
    {
        $activities = $activity->whereOrderId((int)$request->order_id)
            ->when($request->currentDate, function ($q, $date) {
                return $q->where('updated_at', '>=', $date);
            })
            ->when($request->type, function ($q, $type) {
                return $q->where('type', $type);
            });
        if ($request->type && $request->type === 'email') {
            $activities->latest();
        }
        $activities = $activities->get();

        $gmail_messages = $request->client_id ? (new GMailController())->searchMessagesByClient($request->client_id, 0,
            $request->currentDate) : null;

        return response()
            ->json([
                'success' => true,
                'activities' => $activities,
                'gmail_messages' => $gmail_messages ?? null,
                'currentDate' => now()->toDateTimeString(),
            ]);
    }


//    private function getCalculatedSorted($calculated)
//    {
//        $sortCalculated = config('app.calculated_table');
//        return $calculated->sort(function ($a, $b) use ($sortCalculated) {
//            $aSort = !empty($sortCalculated[$a->title]) ? $sortCalculated[$a->title]['sort'] : 0;
//            $bSort = !empty($sortCalculated[$b->title]) ? $sortCalculated[$b->title]['sort'] : 0;
//            return $aSort <=> $bSort;
//        })->values();
//    }

    /**
     * Main order data.
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxInfo(Request $request): JsonResponse
    {

        $order = Order::withInventoriesFormat((int)$request->id)
            ->withWorksFormat()
            ->withWaypointsFormat()
            ->with([
                'foremanNotes.foreman:id,name,l_name',
                'estimate',
                'materials',
                'tags',
                'customsExtras',
                'extended:order_id,miscs'
            ])
            ->findOrFail((int)$request->id);
        $divisionMiscs = session()->get('division.miscs');
        $order->created_at_local_tz = (clone $order->created_at)->tz($divisionMiscs['tz'])->format('Y-m-d H:i:s');
        $order->load([
            'estimate.' . $order->estimate?->type,
            'calculated' => function ($q) use ($order) {
                $q->where('estimate_type', $order->estimate?->type);
            }
        ]);

        $order->setRelation('calculated', EstimateController::getCalculatedSorted($order->calculated));

        // Attaching Tasks to the order
//        $order->tasks = Task::byOrder($order->id)->byDueDate()->get();

        $gmail_messages = $activities_log = [];
        if ($order->client_id) {
            $gmail_messages = (new GMailController())->searchMessagesByClient($order->client_id);
            $activities_log = $order->getMessagesActivity();
        }

        try {
            $zadarma = ['hasApi' => null, 'hasExtension' => null];
            $PBXController = new PBXController;
            $zadarma['hasApi'] = $PBXController->hasZadarma();
            $zadarma['hasExtension'] = $PBXController->getUserPBXExtension();

        } catch (Exception $e) {
        }

        $orderPresets = null;
        $presets = session('presets');
        if (!empty($presets[$order->id])) {
            $orderPresets = $presets[$order->id];
        }

        $service = resolve(AuditFetchService::class);
        if($request->logs_all){
            $logs = $service->byOrderList($order);
        } else {
            $logs = $service->byOrderPagination($order);
        }

        /** @var $servicePayroll PayrollService */
        $servicePayroll = resolve(PayrollService::class);
        $payrollData = $servicePayroll->getDataToCrm($order->id);

        return response()
            ->json([
                'success' => true,
                'id' => (int)request()->get('id'),
                'order' => $order,
                'payroll' => $payrollData,
                'types' => [
                    'works' => WorkTypes::get(['id', 'title'])->keyBy('id'),
//                    'works' => WorkTypeEnum::forSelect(),
                    'tags' => Order\Tag::orderBy('sort')->get(),
                    'waypoints' => [
//                        'flights' => FlightTypeEnum::forSelect(),
//                        'building_types' => BuildingTypeEnum::forSelect(),
//                        'parking_types' => ParkingTypeEnum::forSelect(),
                        'parking_types' => ParkingType::get(['id', 'title'])->keyBy('id'),
                        'building_types' => BuildingType::get(['id', 'title'])->keyBy('id'),
                        'flights' => WaypointFlights::get(['id', 'title', 'sort'])->keyBy('id'),
                    ],
                ],
                'settings' => [
                    'estimate' => EstimateParameters::selected($order->division_id)->get([
                        'id', 'estimate_type', 'name', 'value', 'division_id'
                    ]),
                    'allowed_options' => EstimateController::getAllowedOptions($order->division_id),
                    'zadarma' => $zadarma
                ],
                'activities' => [
                    'messages' => $activities_log,
                    'gmail' => $gmail_messages,
                ],
                'presets' => $orderPresets,
                'logs' => $logs,
                'auth_user' => [
                    'is_partner' => \Auth::user()->isPartner()
                ]
            ]);
    }

    public function ajaxLogs(LogRequest $request): JsonResponse
    {
        $order = Order::find($request->order_id);

        $service = resolve(AuditFetchService::class);
        if($request->logs_all){
            $logs = $service->byOrderList($order, $request->validated());
        } else {
            $logs = $service->byOrderPagination($order, $request->validated());
        }

        return response()
            ->json([
                'success' => true,
                'logs' => $logs,
            ]);
    }

    /**
     * Make a copy of order.
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     *
     * test @see \Tests\Feature\Orders\Order\CopyTest
     */
    public function ajaxCopy(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = Order::findOrFail((int)$request->order_id);

            // copy
            $new = $order->replicate();
            $new->status_id = 12;
            $new->base_id = $order->id;
            $new->hash = Str::random(32);
            // save model before you recreate relations (so it has an id)
            $new->push();

            $order->relations = [];
            $order->load([
                'extended',
                'waypoints',
                'estimate',
//            'notes',
                'services',
                'materials',
                'customsExtras',
                'works', 'works.workTypes',
                'inventories' => function ($q) use ($request) {
                    return $q->where('order_id', $request->order_id);
                },
            ]);

            if($order->estimate){
                $order->load([
                    'estimate.' . $order->estimate->type,
                    'calculated' => function ($q) use ($order) {
                        $q->where('estimate_type', $order->estimate->type);
                    },
                ]);
            }

            $oldKey2NewKeyParentRelation = [];
            foreach ($order->getRelations() as $relationName => $object) {
                if ($object === null) {
                    continue;
                }

                if ($object instanceof Collection) {
                    foreach ($object as $item) {
                        $data = $item->replicate();
                        unset($data->id);
                        if (isset($data->order_id)) {
                            $data->order_id = $new->id;
                        }

                        $mainRelation = $new->{$relationName}();

                        if ($relationName === 'waypoints') {
                            unset($data->notes);
                        } elseif ($relationName === 'works') {
                            unset($data->workTypes);
                        } elseif ($relationName === 'inventories') {
                            unset($data->children);
                            if ($data->section_id) {
                                if (!isset($oldKey2NewKeyParentRelation[$data->section_id])) {
                                    throw new Exception('Unable to get ID in clone ' . $request->order_id);
                                }

                                $data->section_id = $oldKey2NewKeyParentRelation[$data->section_id];
                            }
                        } elseif ($relationName === 'calculated') {
                            $mainRelation->create($data->toArray());
                            continue;
                        }

                        $mainRelation = $mainRelation->forceCreate($data->toArray());
                        $oldKey2NewKeyParentRelation[$item->id] = $mainRelation->id;

                        foreach ($item->getRelations() as $_relation => $_items) {
                            if ($_items === null) {
                                continue;
                            }

                            foreach ($_items as $_item) {
                                $replicatedRelation = $_item->replicate();

                                unset($replicatedRelation->id);
                                if (isset($replicatedRelation->order_id)) {
                                    $replicatedRelation->order_id = $new->id;
                                }

                                if ($relationName === 'waypoints' && $_relation === 'notes') {
                                    $replicatedRelation->waypoint_id = $mainRelation->id;
                                }
                                if ($relationName === 'works' && $_relation === 'workTypes') {
                                    $extra_attributes = Arr::except($_item->pivot->getAttributes(),
                                        $_item->pivot->getForeignKey());
                                    if (method_exists($mainRelation, 'auditAttach'))
                                        $mainRelation->auditAttach($_relation, $_item, $extra_attributes);
                                    else
                                        $mainRelation->{$_relation}()->attach($_item, $extra_attributes);

                                    continue;
                                }
                                $mainRelation->{$_relation}()->forceCreate($replicatedRelation->toArray());
                            }
                        }
                    }
                } else {
                    // hasOne
                    $replicate = $object->replicate();
                    $replicate->order_id = $new->id;

                    $replicate->push();
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            resolve(Handler::class)->report($e);
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'href' => route('orders.record', ['id' => $new->id]),
                'msg' => 'Cloned as ID: #' . $new->id . ' redirecting...'
            ]);
    }

    /**
     *
     * @param Request $request
     * @return void
     */
    public function pipelineRecordsSettingsAjax(Request $request)
    {
        try {
            // current division
            $currentDivision = session('division');

            $Divisions = Division::whereIn('id', $currentDivision['allowed'])->get(['id', 'title']);
            $StatusGroups = Order\StatusGroup::with('statuses:id,group_id')->orderBy('sort')->get([
                'id', 'title', 'sort'
            ])->keyBy('id');
            $Statuses = Order\Status::with('routed')->orderBy('sort')->get([
                'id', 'title', 'color', 'group_id'
            ])->keyBy('id');
            foreach ($Statuses as &$Status) {
                $routes = [];
                if ($Status->routed) {
                    foreach ($Status->routed as $Route) {
                        $routes[] = $Route->route_to_status_id;
                    }
                }
                $Status->routes = $routes;
            }

            foreach ($StatusGroups as &$StatusGroup) {
                $StatusGroup->ordersCount = 0;
                $StatusGroup->loaded = 0;
            }
            $response = [
                'success' => true,
                'groups' => $StatusGroups,
                'statuses' => $Statuses,
                'divisions' => $Divisions,
                'managers' => User::get(['id', 'name']),
                'timezone' => !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : 'UTC'
            ];

        } catch (Exception $e) {
            $response = [
                'success' => false,
                'msg' => $e->getMessage() .
                    (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
            ];
        }

        return response()
            ->json($response);
    }

    public function pipelineRecordsAjax(Request $request)
    {
        $limit = 20;
        try {
            $records = [];
            $totalCount = [];
            $validated = $request->validate([
                'filters.divisions' => 'array',
                'filters.manager' => 'nullable|array',
                'filters.orderByCreated' => 'in:asc,desc',
                'loaded' => 'nullable|array'
                //'groups' => 'nullable',
            ]);
            //dd($validated);
            $currentDivision = session('division');
//            $Timezone = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('timezone');

            //Client::
            $StatusGroups = Order\StatusGroup::with('statuses:id,group_id')->orderBy('sort')->get([
                'id', 'title', 'sort'
            ]);
            foreach ($StatusGroups as $StatusGroup) {
                $totalCount[$StatusGroup->id]['ordersCount'] = Order::whereIn('status_id',
                    $StatusGroup->statuses->pluck('id'))
                    ->where(function (Builder $q) use ($validated) {
                        if (!empty($validated['filters']['manager'])) {
                            $q->whereIn('user_id', $validated['filters']['manager']);
                        }

                        if (!empty($validated['filters']['divisions'])) {
                            $q->whereIn('division_id', $validated['filters']['divisions']);
                        }

                        return $q;
                    })->count();

                $Orders = Order::with([
                    'client', 'tasksInwork:id,order_id', 'tasksOverdue:id,order_id'
                ])->whereIn('status_id', $StatusGroup->statuses->pluck('id'))
                    ->where(function (Builder $q) use ($validated, $StatusGroup) {
                        if (!empty($validated['loaded'][$StatusGroup->id])) {
                            if ($validated['filters']['orderByCreated'] == 'desc') {
                                $q->where('id', '<', +$validated['loaded'][$StatusGroup->id]['min']);
                            } elseif ($validated['filters']['orderByCreated'] == 'asc') {
                                $q->where('id', '>', +$validated['loaded'][$StatusGroup->id]['max']);
                            }
                        }
                        if (!empty($validated['filters']['manager'])) {
                            $q->whereIn('user_id', $validated['filters']['manager']);
                        }
                        if (!empty($validated['filters']['divisions'])) {
                            $q->whereIn('division_id', $validated['filters']['divisions']);
                        }
                    })->orderBy('created_at', $validated['filters']['orderByCreated'])->orderBy('id',
                        $validated['filters']['orderByCreated'])
                    ->take($limit)->get();
//                dd($Orders);
                if ($Orders->count()) {
                    // convert created_at to Division timezone timestamp
                    foreach ($Orders as &$Order) {
                        $CreatedAt = (new Carbon\Carbon($Order->created_at, 'UTC'))->format('U');
                        $Order->timestamp = $CreatedAt;
                    }

                    $records[] = ['groupID' => $StatusGroup->id, 'records' => $Orders];
                }
                //dd($Orders->toArray());
            }
            $response = ['success' => true, 'records' => $records, 'total' => $totalCount];

        } catch (Exception $e) {
            $response = [
                'success' => false,
                'msg' => $e->getMessage() .
                    (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
            ];
        }

        return response()
            ->json($response);

    }

    /**
     * Get data for displaying list of orders.
     * @param Request $request
     * @return Renderable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function records(Request $request)
    {
        abort_if(!Auth::user()->isRoutePatternAllowed('orders.'), 403);

        $d_allowed = session()->get('division.allowed', Auth::user()->division_ids);
        $sources = Source::query()
            ->whereJsonContains('division_ids', request()->session()->get('division.id'))
            ->get(['id', 'title'])
            ->keyBy('id');
        $divisions = Division::whereIn('id', $d_allowed)->get(['id', 'title']);

        $moveSizes = MoveSize::get(['id', 'title'])->keyBy('id');
        $WaypointFlights = WaypointFlights::orderBy('sort')->get(['id', 'title', 'sort']);
        $workTypes = WorkTypes::get(['id', 'title'])->keyBy('id');
        $managers = User::get(['id', 'name', 'active'])->keyBy('id');
        $clientTags = Client\Tag::all(['id', 'title', 'color', 'icon'])->keyBy('id');
        $orderTags = Order\Tag::all(['id', 'title', 'color', 'icon'])->keyBy('id');

        $filteredClients = [];
        if ($request->filled('filter-client')) {
            $filteredClients = Client::whereIn('id', $request->get('filter-client'))->get(['id', 'name', 'lname'])->keyBy('id');
        }

        $filteredOrders = [];
        if ($request->filled('filter-order')) {
            $filteredOrders = Order::whereIn('id', $request->get('filter-order'))->get(['id', 'name', 'lname'])->keyBy('id');
        }

        $newOrders = Order::incomingLeads()->where('division_id', session()->get('division.id'))->count();


        return view('layouts.order.records.body', [
            'statuses' => $this->getStatuses(),
            'sources' => $sources,
            'filteredClients' => $filteredClients,
            'filteredOrders' => $filteredOrders,
            'newOrders' => $newOrders,
            'divisions' => $divisions,
            'moveSizes' => $moveSizes,
            'waypointFlights' => $WaypointFlights,
            'workTypes' => $workTypes,
            'managers' => $managers, // FIXME remove from JS use UserController->usersAjax
            'moveTypes' => config('app.moving_types'),
            'clientTags' => $clientTags,
            'orderTags' => $orderTags,
            'json' => [
                'move-type' => config('app.moving_types'),
                'move-size' => $moveSizes,
                'works' => $workTypes,
                'source' => $sources,
                'manager' => $managers,
                'clientTags' => $clientTags,
                'orderTags' => $orderTags,
                'stages' => $this->getStatuses()->keyBy('id'),
                'tasks' => [
                    'open' => 'With opened Tasks',
                    'not_open' => 'Without opened Tasks',
                ],
                'daterange-type' => [
                    'by-create-date' => 'Creation date',
                    'by-work-date' => 'Service date',
                    'by-transition-date' => 'Stage transition date',
                ],
            ],
        ]);
    }


    /**
     * Creates Order with filled Data or with default.
     * @param $validated
     * @return Order
     */
    public function createEmptyOrder($validated = []): Order
    {
        $order = new Order();
        $move_type = $validated['move-type'] ?? 'local';
        $division_id = request()->session()->get('division.id');

        $min_params = EstimateParameters::selected($division_id)
            ->whereIn('estimate_type', ['any', $move_type])
            ->get(['name', 'value'])
            ->pluck('value', 'name')
            ->all();

        $client_id = $validated['client']['id'] ?? 0;
        if (!$client_id && !empty($validated['client']['name'])) {
            // Created new Client
            $client_id = (new Client())
                ->searchOrCreate($validated['client'], ($validated['client']['email'] ?? ''),
                    ($validated['client']['phone'] ?? ''));
        }

        $source_id = $validated['source'] ?? null;

        // Creating order
        $order->type = $validated['type'] ?? null;
        $order->move_size_id = $validated['move_size_id'] ?? null;
        $order->client_id = $client_id;
        $order->user_id = Auth::check() ? Auth::user()->id : 0;
        $order->division_id = $division_id;
        $order->status_id = 1;
        $order->sizing_is_auto = 1;
        $order->source_id = $source_id;
        $order->hash = Str::random(32);
        $order->save();

        $estimate = [
            'type' => $move_type,
            'trucks' => 1,
            'crews' => 2,
        ];

        if (isset($min_params['fee_type'])) {
            $estimate['fee_type'] = $min_params['fee_type'];
        }
        if (isset($min_params['travel_fee'])) {
            $estimate['travel_fee'] = $min_params['travel_fee'];
        }

        $order->estimate()->create($estimate);

        $work = $order->works()->create([
            'start_date' => $validated['work']['date'] ?? null,
            'duration' => $min_params['default_duration'],
            'trucks' => 1,
            'employees' => 2,
            'in_dispatch' => 0,
        ]);
        $work->auditSync('workTypes', $validated['work']['types'] ?? [1]);
//        $work->workTypes()
//            ->sync($validated['work']['types'] ?? [1]);

        $wp_controller = new WaypointController();

        // Waypoints src
        if (!empty($validated['pickup']['zip']) || !empty($validated['pickup']['address'])) {
            $zip = $validated['pickup']['zip'] ?? null;
            if ($zip) {
                $address = $wp_controller->getAddressInfo((int)$zip);
            }
            $order->waypoints()->create([
                'type' => 'pickup',
                'zip' => substr((int)$zip, 0, 5),
                'city' => $address['address_data']['locality'] ?? null,
                'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                'address' => $validated['pickup']['address'] ?? $address['formatted_address'] ?? null,
                'lat' => $address['geometry']['location']['lat'] ?? null,
                'lng' => $address['geometry']['location']['lng'] ?? null,
                'sort' => 1,
                'building_type_id' => 1,
                'parking_type_id' => 1,
                'flights_id' => $validated['pickup']['stairs'] ?? 0,
                'has_elevator' => $validated['pickup']['elevator'] ? 1 : 0,
            ]);
        }

        // Waypoints dst
        if (!empty($validated['destination']['zip']) || !empty($validated['destination']['address'])) {
            $zip = $validated['destination']['zip'] ?? null;
            if ($zip) {
                $address = $wp_controller->getAddressInfo((int)$validated['destination']['zip']);
            }
            $order->waypoints()->create([
                'type' => 'destination',
                'zip' => substr((int)$zip, 0, 5),
                'city' => $address['address_data']['locality'] ?? null,
                'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                'address' => $validated['destination']['address'] ?? $address['formatted_address'] ?? null,
                'lat' => $address['geometry']['location']['lat'] ?? null,
                'lng' => $address['geometry']['location']['lng'] ?? null,
                'sort' => 2,
                'building_type_id' => 1,
                'parking_type_id' => 1,
                'flights_id' => $validated['destination']['stairs'] ?? 0,
                'has_elevator' => $validated['destination']['elevator'] ? 1 : 0,
            ]);
        }

        // Calculate route
        if (!empty($validated['pickup']['zip']) && !empty($validated['destination']['zip'])) {
            $wp_controller->recalculateDistance($order);
        }

        $order->statusHistory()
            ->create([
                'prev_status' => 0,
                'new_status' => 1,
                'user_id' => $order->user_id,
                'created_at' => now()->toDateTimeString()
            ]);

        RecordCreateService::handler($order);

        return $order;
    }

    /**
     * Creating a new order + Client if necessary.
     * @param CreateOrderRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create(CreateOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            DB::beginTransaction();

            $order = $this->createEmptyOrder($validated);

            // закомментировали по желанию клиента
//            if (!empty($validated['client']['name']) && (!empty($validated['client']['phone']) || !empty($validated['client']['email']))) {
//                ZaiperNewLeadJob::dispatch([
//                    'client_id' => $order->client_id,
//                    'leadID' => $order->id,
//                ]);
//            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'msg' => 'Order created',
                'redirect' => route('orders.record', ['id' => $order->id]),
            ]);
    }

    /**
     * Get Records for DataTable with filters.
     * @param Request $request
     * @return JsonResponse
     */
    public function recordAjaxDT(Request $request): JsonResponse
    {
        $collection = $this->order->getOrdersDT($request);
        $ordersTransformed = $this->transformOrdersRecords($collection);

        return response()
            ->json([
                'success' => true,
                'data' => $ordersTransformed->toArray(),
            ]);
    }

    /**
     * Transform order data.
     * @param DBCollection $recordsCollection
     * @return DBCollection|Collection
     */
    private function transformOrdersRecords(DBCollection $recordsCollection)
    {
        $PBXController = new PBXController();
        /**
         * @var $emailActivity DBCollection
         */
        $emailActivity = Order\Activity::whereIn('order_id', $recordsCollection->pluck('id'))
            ->where('type', '=', 'email')->latest()->get()->countBy(function ($record) {
                return $record['order_id'];
            });

        $columns = ['order', 'work', 'waypoints', 'estimate', 'client', 'manager'];
        $status_records = Order\Status::get(['id', 'title', 'color'])->keyBy('id');
        $status_routes = Order\StatusRoute::get(['status_id', 'route_to_status_id'])->groupBy('status_id');
        return $recordsCollection
            ->map(function (Order $item) use (
                $columns,
                $status_routes,
                $status_records,
                $PBXController,
                $emailActivity
            ) {
                // Change the key and select the correct set of calculations
                $item->setRelation('calculated',
                    $item->calculated->where('estimate_type', $item->estimate->type)->keyBy('title'));

                $all_works = [];
                $item->works
                    ->each(function ($work) use (&$all_works) {
                        $work->workTypes
                            ->each(function ($v, $k) use (&$all_works) {
                                $all_works[$k] = $v->title;
                            });
                    });
                $item->all_works = $all_works;

                $communicationsCount = [
                    'callsSucceed' => 0,
                    'callsFailed' => 0,
                    'emails' => (int)$emailActivity->get($item->id)
                ];

                if ($item->client_id && $item->client->phones->count() > 0) {
                    $zadarmaRecords = [];
                    $PBXController->getZadarmaPhoneLog($item, $item->client->phones->pluck('value')->toArray(),
                        $zadarmaRecords);
                    if (!empty($zadarmaRecords)) {
                        foreach ($zadarmaRecords as $r) {
                            if ($r['disposition'] === 'answered') {
                                $communicationsCount['callsSucceed']++;
                            } else {
                                $communicationsCount['callsFailed']++;
                            }
                        }
                    }
                }

                $newItem = [
                    'details' => [
                        'id' => $item->id,
                        'division' => $item->division,
//                        'created_at' => $item->created_at_current_timezone->format('M d, Y \a\t g:i A \(e\)'),
                        'created_at' => $item->created_at_division_timezone->format('M d, Y \a\t g:i A \(e\)'),
                        'url' => route('orders.record', ['id' => $item->id]),
                        'status' => $item->status,
                        'status_routes' => $this->getStatusRoutes($item->status->id, $status_records, $status_routes),
                        'estimate' => ucfirst($item->estimate->type),
                        'tasks_count' => $item->tasks_count,
                        'order_tags' => $item->tags,
                    ]
                ];
                foreach ($columns as $column) {
                    $newItem[$column] = view('layouts.order.records.itemDT.' . $column, [
                        'record' => $item,
                        'communicationsCount' => $communicationsCount
                    ])->render();
                }
                return $newItem;
            });
    }

    /**
     * Get Records for DataTable with filters.
     * FIXME recordsAjaxDT vs recordAjaxDT
     * @param Request $request
     * @return JsonResponse
     */
    public function recordsAjaxDT(Request $request): JsonResponse
    {
        $recordsCollection = $this->order->getOrdersDT($request);
        $ordersTransformed = $this->transformOrdersRecords($recordsCollection->getCollection());

        return response()
            ->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $recordsCollection->total(),
                'recordsFiltered' => $recordsCollection->total(),
                'data' => $ordersTransformed->toArray(),
            ]);
    }

    /**
     * Get status routes.
     * @param $fromID
     * @param Collection $records
     * @param Collection $routes
     * @return Collection
     */
    private function getStatusRoutes($fromID, Collection $records, Collection $routes)
    {
        return $records->filter(function ($value, $key) use ($fromID, $routes) {
            if ($routes->get($fromID)) {
                return $routes->get($fromID)->pluck('route_to_status_id')->contains($key);
            }
            return null;
        });
    }

    /**
     * Get record data for Order record page.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function record(int $id, Request $request)
    {
        $order = $this->order->withCount('notes')->findOrFail($id);

        $d_allowed = $request->session()->get('division.allowed', Auth::user()->division_ids);
        if (!in_array($order->division_id, $d_allowed, true) || !Auth::user()->isRoutePatternAllowed('orders.')) {
            self::message('You don\'t have permissions for open this order');

            return redirect()->route('orders.records');
        }

        $managers = User::where(function (Builder $q) use ($order) {
            return $q->where(function (Builder $q) {
                return $q->whereHas('roles', function ($q) {
                    return $q->orderManager();
                });
            })
                ->orWhere('id', $order->user_id);
        })->get(['id', 'name'])->keyBy('id');

        $closingStatuses = OrderClosingStatus::with('group:id,title,sort')
            ->orderBy('sort', 'ASC')->orderBy('title', 'ASC')
            ->get(['id', 'title', 'sort', 'group_id']);
        $closingStatuses = $closingStatuses->mapToGroups(function ($item, $k) {
            return [$item->group ? $item->group->title : 'No group' => $item];
        })->map(function (Collection $item) {
            return $item->mapWithKeys(function ($v, $k) {
                return [$v->id => $v->title];
            });
        });

//        dump($closingStatuses->toArray());
        return view('layouts.order.record.body', [
            'record' => $order,
            'statuses' => $this->getStatuses(),
            'order_managers' => $managers,
            'email_templates_v2' => EmailTemplateGroup::records($order->division_id)->get(),
            'closing_statuses' => OrderClosingStatus::with('group')->get(['id', 'title'])->pluck('title', 'id')->all(),
            'closing_statuses_with_groups' => $closingStatuses,
            'breadcrumbs' => [
                [
                    'href' => route('orders.records'),
                    'title' => 'Orders',
                ],
                [
                    'title' => 'Record',
                ]
            ],
        ]);
    }

    /**
     * Close order.
     * @param Request $request
     * @param Order $_order
     * @return JsonResponse
     * @throws \Throwable
     */
    public function setStatusClosed(Request $request, Order $_order): JsonResponse
    {
        $validatedData = $request->validate([
            'order_id' => 'required|numeric',
            'closing_reason_id' => 'required|numeric|exists:settings_closing_statuses,id',
        ]);

        $order = $_order->with('extended')->findOrFail($validatedData['order_id']);

        $close_request = (new Request())
            ->replace([
                'order_id' => $validatedData['order_id'],
                'status_id' => 9,
                'old_status' => $order->status_id,
                'is_roll_back' => 0,
            ]);

        $miscs = $order->extended->miscs ?? [];
        $miscs['order']['closing_reason_id'] = (int)$validatedData['closing_reason_id'];

        try {
            DB::beginTransaction();
            $this->setStatus($close_request);
            $order->extended()
                ->updateOrCreate(['order_id' => $validatedData['order_id']], ['miscs' => $miscs]);
            DB::commit();
            return response()
                ->json([
                    'success' => true,
                    'msg' => 'Order Closed',
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }
    }

    /**
     * test @see \Tests\Feature\Orders\Statuses\SetTest (дополнить тесты)
     */
    public function setStatus(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'order_id' => 'required|numeric',
            'old_status' => 'required|numeric',
            'is_roll_back' => 'required|numeric',
            'status_id' => 'required|numeric|exists:orders_statuses,id',
        ]);

        $order = Order::with('statusHistory')->findOrFail($validatedData['order_id']);
        $new_status = Order\Status::find($validatedData['status_id']);

        if ($order->status_id !== $validatedData['old_status']) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => 'Can\'t save, order was changed by someone',
                ]);
        }

        try {
            DB::beginTransaction();

            $resp = [
                'success' => true,
                'msg' => 'Status changed',
            ];
            $order->status_id = $validatedData['status_id'];
            if ($validatedData['is_roll_back']) {
                $history = $order->statusHistory()->latestNotDeleted()->first();
                if ($history) {
                    $history->update(['is_deleted' => 1]);
                }
            } else {
                $order->statusHistory()
                    ->create([
                        'prev_status' => $validatedData['old_status'],
                        'new_status' => $validatedData['status_id'],
                        'user_id' => Auth::id(),
                        'created_at' => now()->toDateTimeString()
                    ]);
            }

            $history = $order->statusHistory()->latestNotDeleted()->first();
            $resp['prev_status'] = $history->prev_status ?? 0;

            // Set work as Dispatched
            if ($new_status->enable_dispatch) {
                $resp['reload_works'] = (new Order\Work())->setAsDispatched($validatedData['order_id']);
            }

            // When Cancellation - remove from dispatch
            if ($new_status->disable_dispatch) {
//                dd($new_status->disable_dispatch);
                $resp['reload_works'] = (new Order\Work())->setAsCancellation($validatedData['order_id']);
            }

            $order->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }

        return response()
            ->json($resp);
    }

    /**
     * Save simple order data.
     * @param OrderRequest $request
     * @return JsonResponse
     * @throws \Throwable
     *
     * test @see \Tests\Feature\Orders\Order\UpdateTest (дополнить тесты)
     */
    public function saveOrder(OrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $order_id = $request->route('id');

        $order = Order::findOrFail($order_id);

        $order->user_id = $validated['user_id'] ?? $order->user_id;
        $order->division_id = $validated['division_id'] ?? $order->division_id;
        $order->source_id = $validated['source_id'] ?? $order->source_id;
        $order->move_size_id = $validated['move_size_id'] ?? $order->move_size_id;
        $order->type = $validated['type'] ?? $order->type;

        if ((new Order\Tag())->tagsSaver($order, $validated['selectedTags'] ?? [])) {
            $dirty = true;
        }

        if ($order->isDirty()) {
            $order->updated_by = Auth::user()->id;
            $dirty = true;

            try {
                $order->save();
            } catch (Exception $e) {
                return response()
                    ->json([
                        'success' => false,
                        'msg' => $e->getMessage()
                    ]);
            }
        }

        return response()
            ->json([
                'success' => true,
                'msg' => isset($dirty) ? 'Order changed' : 'Changed nothing'
            ]);
    }

    /**
     * Get order routes and statuses.
     * @return JsonResponse
     */
    public function ajaxOrderStatusesInfo(): JsonResponse
    {
        $status_records = Order\Status::get(['id', 'title', 'color', 'group_id'])->keyBy('id');
        $status_groups = Order\StatusGroup::orderBy('sort')->get();
        $status_routes = Order\StatusRoute::get(['status_id', 'route_to_status_id'])->groupBy('status_id');

        return response()
            ->json([
                'success' => true,
                'status' => [
                    'records' => $status_records,
                    'groups' => $status_groups,
                    'routes' => $status_routes,
                    'prev_status' => 0,
                ]
            ]);
    }

    /**
     * Main data about typesets.
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxInfoOrder(Request $request): JsonResponse
    {
        $division_id = Order::find($request->order_id, ['division_id'])->division_id;

        $sources = Source::query()
            ->whereJsonContains('division_ids', $division_id)->get(['id', 'title']);
        $divisions = Division::get(['id', 'title']);
//        $moveSizes = MoveSize::get(['id', 'title']);
        $status_records = Order\Status::get(['id', 'title', 'color'])->keyBy('id');
        $status_routes = Order\StatusRoute::order()->get(['status_id', 'route_to_status_id'])->groupBy('status_id');
        $last_status = Order\StatusChangeHistory::whereOrderId($request->order_id)
            ->latestNotDeleted()
            ->first('prev_status');

        return response()
            ->json([
                'success' => true,
                'dataSources' => [
                    'sources' => $sources,
                    'divisions' => $divisions,
                    'moveSizes' => MoveSizeTypeEnum::forSelect(),
                    'moveTypes' => config('app.moving_types'),
                ],
                'status' => [
                    'records' => $status_records,
                    'routes' => $status_routes,
                    'prev_status' => $last_status->prev_status ?? 0,
                ]
            ]);
    }

    /**
     * Changing in order client. If client not exists - creating new.
     * @param ChangeClientRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function changeClient(ChangeClientRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use (&$client, $validated) {
                if ($validated['id']) {
                    $client = Client::find($validated['id']);
                } else {
                    // Create new client
                    $client = Client::create($validated);

                    if ($validated['phone']) {
                        $client->phones()->create([
                            'value' => $validated['phone'],
                            'is_primary' => 1,
                            'sort' => 1,
                            'type_id' => 1,
                        ]);
                    }

                    if ($validated['email']) {
                        $client->emails()->create([
                            'value' => $validated['email'],
                        ]);
                    }
                }

                $order = Order::find($validated['order_id']);
                if ($order->client_id !== $client->id) {
                    $order->client_id = $client->id;
                    $order->save();
                }

                RecordUpdateService::client($order, $client);
            });
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }


        return response()
            ->json([
                'success' => true,
                'client_id' => $client->id,
            ]);
    }

    /**
     * Search order by id + paginate.
     * @param Request $request
     * @param Order $order
     * @return JsonResponse
     */
    public function ajaxAutocomplete(Request $request, Order $order): JsonResponse
    {
        $division_id = request()->session()->get('division.id');

        $page = filter_var($request->page ?? 1, FILTER_SANITIZE_NUMBER_INT);

        $pageLimit = 15;
        $offset = ($page - 1) * $pageLimit;

        $q = $order->query()
            ->with([
                'client:id,name,lname',
                'client.emails',
                'client.phones',
                'status',
                'division:id,title',
            ])
            ->when($request->get('division_id'), function ($q, $division_id) {
                $q->where('division_id', $division_id);
            })
            ->when(is_null($request->get('division_id')) && $division_id, function ($q, $division_id) {
                $q->where('division_id', $division_id);
            })
            ->where(function (Builder $query) use ($request) {
                $term = $request->q;

                if (preg_match('/^\#[0-9]+$/', $term)) {
                    $query->where('id', 'LIKE', substr($term, 1) . '%');
                    return $query;
                }
                if (is_numeric($term)) {
                    $query->where('id', 'LIKE', $term . '%');
                }

                if ($request->has('interface') && $request->interface === 'orders') {
                    return $query;
                }

                if (is_numeric($term) && strlen($term) > 3) {
                    $query->orWhereHas('client.phones', function (Builder $q) use ($term) {
                        return $q->where('value', 'like', '%' . $term . '%');
                    });
                } elseif (!is_numeric($term) && strlen($term) >= 2) {
                    $query->whereHas('client', function (Builder $query) use ($term) {
                        $query
                            ->whereRaw("CONCAT(`name`, ' ', `lname`) LIKE ?", ['%' . $term . '%'])
                            ->orWhereHas('emails', function ($q) use ($term) {
                                $q->where('value', 'like', '%' . $term . '%');
                            });
                    });
                }
                return $query;
            });

        $search_count = (clone $q)->count();
        $search = (clone $q)->skip($offset)
            ->orderByDesc('id')
            ->take($pageLimit)
            ->get(['id', 'status_id', 'client_id', 'division_id', 'created_at']);


        return response()
            ->json([
                'success' => true,
                'results' => array_values($search->toArray()),
                'pagination' => [
                    'more' => ceil($search_count / $pageLimit) > $page
                ]
            ]);
    }

    public function export()
    {
        return Excel::download(new OrdersExport(), 'orders.xlsx');
    }

}
