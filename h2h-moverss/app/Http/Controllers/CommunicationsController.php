<?php

namespace App\Http\Controllers;

use App\DataTables\CallLogDataTable;
use App\Enums\Communications\Type;
use App\Events\ConversationMarkEvent;
use App\Http\Controllers\Communications\RecordController;
use App\Http\Controllers\Zadarma\PBXController;
use App\Http\Requests\Communications\MarkConversationRequest;
use App\Services\Communications\RecordCreateService;
use App\Models\{Client,
    Communications\CommunicationRecord,
    Communications\ConversationFavorites,
    Communications\ConversationMark,
    CommunicationsIgnoreList,
    Employee,
    Mailbox\Gmail\Account,
    Mailbox\Gmail\Message,
    Order,
    Ringostat\EventAfterCall,
    Twilio\TwilioSms,
    Zadarma\CallsEvents,
    Zadarma\SmsEvents};
use App\Traits\ResponseFormatter;
use App\User;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunicationsController extends Controller
{
    use ResponseFormatter;

    public function callLog(CallLogDataTable $dataTable, User $user)
    {
        $divisionMiscs = session()->get('division.miscs');
        // users with extensions
        $users = $user::query()
            ->whereHas('employee.pbxdata', function (Builder $query) use ($divisionMiscs) {
                $query->where('pbx_id', $divisionMiscs['zadarma_pbx_id'])->where('pbx_ext', '>', 0);
            })
            ->with(['employee'])
            ->whereJsonContains('division_ids', [(int)session()->get('division.id')])
            ->orderBy('name')
            ->get();

        return $dataTable
            ->render('layouts.data-tables.call-log', [
                'users' => $users,
            ]);
    }

    /**
     *
     * @param $Object
     * @param $DateTime
     * @return mixed
     */
    private function updateSinceDate($Object, $DateTime)
    {
        if ($Object->datetime > $DateTime) {
            return $Object->datetime;
        }
        return $DateTime;
    }

    public function addIgnoreContactAjax(Request $request): JsonResponse
    {
        $response = [
            'success' => false
        ];
        try {
            $validated = $request->validate([
                'relation' => 'required|in:emails,phones',
                'value' => 'required|string',
            ]);

            CommunicationsIgnoreList::query()
                ->updateOrCreate([
                    'type' => $validated['relation'],
                    'value' => $validated['value'],
                ]);
            $response = ['success' => true];
        } catch (Exception $e) {
            //dump($e);
            $response['msg'] = $e->getMessage();
        }
        return response()
            ->json($response);
    }


    public function createOrderToClientAjax(Request $request)
    {
        \Log::info('Create order TO client from com-panel START', ['request' => $request->all()]);
        $response = [
            'success' => false
        ];
        try {
            $validated = $request->validate([
                'clientID' => "required|exists:App\Models\Client,id",
            ]);
            DB::beginTransaction();

            $Order = (new OrderController(new Order()))->createEmptyOrder();
            $Order->client_id = $validated['clientID'];
            $Order->source_id = $this->getSourceIdForOrder(clientId: $validated['clientID']);
            $Order->save();
            \Log::info('Create order #' . $Order->id);
            if(
                $comRecord = CommunicationRecord::query()
                    ->where('client_id', $validated['clientID'])
                    ->where('is_answered', false)
                    ->latest()
                    ->first()
            ){
                $comRecord->update(['is_answered' => true]);
                \Log::info('Update com-record', ['record' => $comRecord->toArray()]);
            }

            DB::commit();
            $response = ['success' => true, 'orderID' => $Order->id];
        } catch (Exception $e) {
            DB::rollBack();
            $response['msg'] = $e->getMessage();
            \Log::error('Create order TO client from com-panel ERROR', [$e]);
        }

        \Log::info('Create order TO client from com-panel FINISH');

        return response()
            ->json($response);
    }

    protected function getSourceIdForOrder($clientId = null, $value = null): ?int
    {
        $divisionId = session()->get('division.id');
        $targetPhones = [
            1 => ['17732321892'],
            2 => ['12132210157']
        ];

        \Log::info('Get source ID for order', [
            'client_id' => $clientId,
            'value' => $value,
            'division_id' => $divisionId,
            'target_phones' => $targetPhones,
        ]);

        $sourceId = null;
        $divisionId = session()->get('division.id');
        $targetPhones = [
            1 => ['17732321892'],
            2 => ['12132210157']
        ];

        if(!is_null($clientId)){
            $client = Client::query()
                ->with('phones')
                ->where('id', $clientId)
                ->first()
                ->toArray()
            ;
            $filter = [
                'contact' => [
                    'client' => $client
                ],
            ];
        } elseif (
            !is_null($value)
        ) {
            $filter = [
                'contact' => [
                    'client' => null,
                    'channelContact' => $value,
                ],
            ];
        } else {
            return $sourceId;
        }

        \Log::info('Get source ID for order', [
            'filter' => $filter,
        ]);

        /** @var $controller RecordController */
        $controller = resolve(RecordController::class);
        $recs = $controller->getFlowRecPagination(
            filter: $filter,
            limit: 1,
            offset: 0
        );
        if($recs->isNotEmpty()){
            /** @var $rec CommunicationRecord */
            $rec = $recs[0];
            if($rec->isRingostatCall() && $rec->type->isInbound() ){
                if(in_array($rec->entity->destination, $targetPhones[$divisionId])){
                    if(
                        $source = Order\Source::query()
                            ->where('title', Order\Source::GOOGLE_GUARANTEE_NAME)
                            ->where('division_ids', "[$divisionId]")
                            ->first()
                    ){
                        \Log::info('Set source - ' . Order\Source::GOOGLE_GUARANTEE_NAME);
                        $sourceId = $source->id;
                    }
                }
            }
        }

        return $sourceId;
    }

    public function createClientOrderRelationRecordAjax(Request $request): JsonResponse
    {
        \Log::info('Create order and client from com-panel START', ['request' => $request->all()]);

        $response = [
            'success' => false
        ];
        try {
            $sourceId = null;
            $validated = $request->validate([
                'relation' => "required|in:emails,phones",
                'value' => "required|string",
            ]);

            if($validated['relation'] === 'phones'){
                $sourceId = $this->getSourceIdForOrder(value: $validated['value']);
            }

            DB::beginTransaction();
            $Client = Client::create(['name' => 'Noname', 'lname' => '']);
            $Client->{$validated['relation']}()->create(['value' => $validated['value']]);

            $Order = (new OrderController(new Order()))->createEmptyOrder();
            $Order->client_id = $Client->id;
            $Order->source_id = $sourceId;
            $Order->save();

            \Log::info('Create order #' . $Order->id . ' [create client #'.$Client->id . ']');

            // обновляем последнюю комуникацию
            if(
                $comRecord = CommunicationRecord::query()
                    ->where('channel_contact', $validated['value'])
                    ->latest()
                    ->first()
            ){
                if(is_null($comRecord->order_id)){
                    $comRecord->order_id = $Order->id;
                }
                if(is_null($comRecord->client_id)){
                    $comRecord->client_id = $Client->id;
                }
                if(!$comRecord->is_answered){
                    $comRecord->is_answered = true;
                }
                $comRecord->save();
                \Log::info('Update com-record', ['record' => $comRecord->toArray()]);
            }

            $response['client'] = Client::with(['phones', 'emails'])->withCount('orders')->findOrFail($Client->id);
            $response['orderID'] = $Order->id;
            DB::commit();

            $response ['success'] = true;
        } catch (Exception $e) {
            DB::rollBack();
            $response['msg'] = $e->getMessage();
            \Log::error('Create order and client from com-panel ERROR', [$e]);
        }
        \Log::info('Create order and client from com-panel FINISH');

        return response()
            ->json($response);
    }

    public function addClientRelationRecordAjax(Request $request)
    {
        $response = [
            'success' => false
        ];
        try {
            $validated = $request->validate([
                'orderID' => 'required|exists:App\Models\Order,id',
                'relation' => 'required|in:emails,phones',
                'value' => 'required|string',
            ]);
            DB::beginTransaction();

            $Order = Order::with(['client', 'client.' . $validated['relation']])->findOrFail($validated['orderID']);
            if ($Order->client) {
                $Order->client->{$validated['relation']}()->create(['value' => $validated['value']]);
                $clientID = $Order->client->id;
            } else {
                $Client = Client::create(['name' => 'Noname']);
                $Client->{$validated['relation']}()->create(['value' => $validated['value']]);
                $Order->client_id = $Client->id;
                $Order->save();
                $clientID = $Client->id;
            }
            $response['client'] = Client::withCount('orders')->findOrFail($clientID);

            DB::commit();
            $response['success'] = true;
        } catch (Exception $e) {
            //dump($e);
            DB::rollBack();
            $response['msg'] = $e->getMessage();
        }

        return response()
            ->json($response);
    }

    /**
     * test @see \Tests\Feature\Communications\Old\MarkStarredTest
     */
    public function markStarred(Request $request)
    {
        $response = [
            'success' => false
        ];
        try {
            $requestData = $request->all();

//            dd($requestData);

//            dd($requestData, !empty($requestData['conversation']['client']));
            if (!empty($requestData['conversation']['client'])) {
                $ConversationFavorite = ConversationFavorites::updateOrCreate(
                    [
                        'client_id' => $requestData['conversation']['client']['id'],
                        'user_id' => Auth::id(),
                    ],
                    [
                        'starred' => !empty($requestData['starred']) ? 1 : 0
                    ]
                );
            } elseif (!empty($requestData['conversation']['channelContact'])) {
                $createData = [
                    'user_id' => Auth::id(),
                    'starred' => !empty($requestData['starred']) ? 1 : 0,
                    'contact_type' => '',
                    'contact_value' => $requestData['conversation']['channelContact']
                ];
                $searchData = [
                    'user_id' => Auth::id(),
                    'contact_type' => '',
                    'contact_value' => $requestData['conversation']['channelContact']
                ];
                if ($requestData['conversation']['type'] == 'CallsEvents' || $requestData['conversation']['type'] == 'TwilioSms') {
                    $createData['contact_type'] = $searchData['contact_type'] = 'phone';
                } elseif ($requestData['conversation']['type'] == 'Message') {
                    $createData['contact_type'] = $searchData['contact_type'] = 'email';
                }
                if (empty($createData['contact_type']) || empty($createData['contact_value']))
                    throw new Exception('Conversation mark without valid contact!');
                $ConversationFavorite = ConversationFavorites::updateOrCreate($searchData, $createData);
            } else {
                throw new Exception('Conversation mark without valid contact!');
            }
//            broadcast(new ConversationMarkEvent($ConversationMark, session('division.id')));

            if(isset($requestData['conversation']['id'])){
                $ConversationFavorite->update([
                    'communication_rec_id' => $requestData['conversation']['id']
                ]);
            } elseif (
                $requestData['conversation']['type']
                && $requestData['conversation']['uid']
            ) {

                $type = null;
                if($requestData['conversation']['type'] == 'TwilioSms'){
                    $type = TwilioSms::MORPH_NAME;
                }
                if($requestData['conversation']['type'] == 'EventAfterCall'){
                    $type = EventAfterCall::MORPH_NAME;
                }
                if($requestData['conversation']['type'] == 'Message'){
                    $type = Message::MORPH_NAME;
                }
                if($requestData['conversation']['type'] == 'SmsEvents'){
                    $type = SmsEvents::MORPH_NAME;
                }
                if($requestData['conversation']['type'] == 'CallsEvents'){
                    $type = CallsEvents::MORPH_NAME;
                }
                $id = last(explode('-', $requestData['conversation']['uid']));

                if(
                    $model = CommunicationRecord::query()
                        ->select('id')
                        ->where('entity_type', $type)
                        ->where('entity_id', $id)
                        ->first()
                ){
                    $ConversationFavorite->update([
                        'communication_rec_id' => $model->id
                    ]);
                }
            }

            $response['success'] = true;
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }
        return response()
            ->json($response);
    }

    /**
     * test @see \Tests\Feature\Communications\Old\MarkConversationTest
     */
    public function markConversation(MarkConversationRequest $request)
    {
        $response = [
            'success' => false
        ];

        try {
            $requestData = $request->validated();

            if (!empty($requestData['conversation']['client'])) {
                $createData = [
                    'client_id' => $requestData['conversation']['client']['id'],
                    'user_id' => Auth::id(),
                    'type' => $requestData['type']
                ];
            } elseif (!empty($requestData['conversation']['channelContact'])) {
                $createData = [
                    'user_id' => Auth::id(),
                    'type' => $requestData['type'],
                    'contact_type' => '',
                    'contact_value' => $requestData['conversation']['channelContact']
                ];

                if (
                    $requestData['conversation']['type'] == 'CallsEvents'
                    || $requestData['conversation']['type'] == 'TwilioSms'
                    || $requestData['conversation']['type'] == 'EventAfterCall'
                    || $requestData['conversation']['type'] == 'SmsEvents'
                ) {
                    $createData['contact_type'] = 'phone';
                } elseif ($requestData['conversation']['type'] == 'Message') {

                    $createData['contact_type'] = 'email';
                }
                if (empty($createData['contact_type']) || empty($createData['contact_value'])){
                    throw new Exception('Conversation mark without valid contact!');
                }

            } else {
                throw new Exception('Conversation mark without valid contact!');
            }

            $conversationMark = ConversationMark::create($createData);

            RecordCreateService::handler($conversationMark, ['division_id' => session('division.id')]);


            if(isset($requestData['conversation']['id'])){
                $comRec = CommunicationRecord::query()
                    ->where('id', $requestData['conversation']['id'])
                    ->first();

                $comRec->update(['is_answered' => true]);

                CommunicationRecord::query()
                    ->where('channel_contact', $comRec->channel_contact)
                    ->where('is_answered', false)
                    ->where('sort_at', '<=', $comRec->sort_at)
                    ->update(['is_answered' => true]);

            } else {
                // @todo
                // это сделано под старую реализацию
                // когда перейдем на новую версию коммуникационной панели, код ниже убрать

                $type = $requestData['conversation']['type'];
                $id = last(explode('-', $requestData['conversation']['uid']));
                $entityType = null;

                if($type === 'TwilioSms'){
                    $entityType = TwilioSms::MORPH_NAME;
                }
                if($type === 'EventAfterCall'){
                    $entityType = EventAfterCall::MORPH_NAME;
                }
                if($type === 'CallsEvents'){
                    $entityType = CallsEvents::MORPH_NAME;
                }
                if($type === 'SmsEvents'){
                    $entityType = SmsEvents::MORPH_NAME;
                }
                if($type === 'Message'){
                    $entityType = Message::MORPH_NAME;
                }

                CommunicationRecord::query()
                    ->where('entity_type', $entityType)
                    ->where('entity_id', $id)
                    ->update(['is_answered' => true]);
            }

            broadcast(new ConversationMarkEvent($conversationMark, session('division.id')));

            $response['success'] = true;
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return response()
            ->json($response);
    }

    public function flow(Request $request): JsonResponse
    {
        $response = [
            'success' => false
        ];
        try {
            $limit = 20;
            $channelContactType = null;
            $records = collect([]);
            $validated = $request->validate([
                'untill' => 'nullable|int',
                'contact.type' => 'string',
                'contact.client' => 'nullable|array',
//                'contact.channelContact' => 'nullable|integer|string',
            ]);
            $validated = $request->all();
            $where = [
                'phones' => [],
                'emails' => []
            ];

            // detect client or channel
            if ($validated['contact']['client']) {
                $Client = Client::with(['emails', 'phones'])->findOrFail($validated['contact']['client']['id']);
                if ($Client->emails->isNotEmpty()) {
                    $where['emails'] = $Client->emails->pluck('value')->toArray();
                }
                if ($Client->phones->isNotEmpty()) {
                    $where['phones'] = $Client->phones->pluck('value')->toArray();
                }
                //$Phone = Client\Phone::
            } elseif (in_array($validated['contact']['type'], ['CallsEvents', 'EventAfterCall', 'TwilioSms', 'SmsEvents'])) {
                $where['phones'][] = $validated['contact']['channelContact'];
                $channelContactType = 'phone';
                if (Str::startsWith($validated['contact']['channelContact'], 'Anonymous'))
                    $where['zadarma_ids'][] = preg_replace("/[^0-9]/", "", $validated['contact']['channelContact']);

            } elseif ($validated['contact']['type'] === 'Message') {
                $where['emails'][] = $validated['contact']['channelContact'];
                $channelContactType = 'email';
            }


            $Till = $validated['untill'] ?
                CarbonImmutable::createFromTimestamp($validated['untill'], 'UTC')
                : CarbonImmutable::now('UTC');
            $Since = new CarbonImmutable('2021-01-01', 'UTC');

            $currentDivision = session('division');
            $Timezone = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('app.timezone');

            // Calls
            if (!empty($where['phones'])) {
                $PBXController = new PBXController();
                $PBXController->hasZadarma();

                if ($PBXController->getPBXid()) {
                    $ZadarmaBuilder = CallsEvents::where(function (Builder $q) use ($where) {
                        $q->where(function (Builder $query) use ($where) {
                            return $query->where('event', '=', 'NOTIFY_END')
                                ->where(function ($query) use ($where) {
                                    foreach ($where['phones'] as $phone) {
                                        $query->orWhere('caller_id', 'LIKE', '%' . $phone);
                                    }
                                });

                        })->orWhere(function ($query) use ($where) {
                            return $query->where('event', '=', 'NOTIFY_OUT_END')
                                ->where(function ($query) use ($where) {
                                    foreach ($where['phones'] as $phone) {
                                        $query->orWhere('destination', 'LIKE', '%' . $phone);
                                    }
                                });
                        });
                        if (!empty($where['zadarma_ids'])) {
                            $q->orWhereIn('id', $where['zadarma_ids']);
                        }
                    });
                    $Builder = (clone $ZadarmaBuilder)->where('call_start', '<',
                        $Till->setTimezone($Timezone)->toDateTimeString())
                        ->with(['internalPbxData:employee_id,pbx_ext,pbx_id', 'internalPbxData.employee:id,name,l_name'])
//                    ->with(['internalEmployee:id,name,l_name'])
                        ->where('call_start', '>', $Since->setTimezone($Timezone)->toDateTimeString())
                        ->whereIn('event', ['NOTIFY_END', 'NOTIFY_OUT_END'])
                        ->orderBy('call_start', 'DESC')
                        ->limit($limit);
                    $zadarmaRecords = $this->filterIgnoredCallRecords($Builder)->get();

                    if ($zadarmaRecords->isNotEmpty()) {
                        foreach ($zadarmaRecords as $record) {
                            $records->push(
                                $this->getCommunicationPanelFormat($record, $Timezone)
                            );
                        }
                        if ($zadarmaRecords->count() == $limit) {
                            $Since = $this->updateSinceDate($records->last(), $Since);
                        }
                    }

                    //ZadarmaSMS
                    $phones = $where['phones'];
                    $SmsEventsBuilder = SmsEvents::where(function (Builder $q) use ($phones) {
                        $q->where(function (Builder $query) use ($phones) {
                            return $query->where('inbound', '=', 1)
                                ->where(function ($query) use ($phones) {
                                    foreach ($phones as $phone) {
                                        $query->orWhere('caller_id', 'LIKE', '%' . $phone);
                                    }
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
                    })->where('pbx_id', $PBXController->getPBXid());
                    $Builder = (clone $SmsEventsBuilder)->where('created_at', '<', $Till->toDateTimeString())
                        ->where('created_at', '>', $Since->toDateTimeString())
                        ->orderBy('created_at', 'DESC')
                        ->limit($limit);
                    $ZadarmaSMSRecords = $this->filterIgnoredTwilioRecords($Builder)->get();

                    if ($ZadarmaSMSRecords->isNotEmpty()) {
                        foreach ($ZadarmaSMSRecords as $record) {
                            $records->push(
                                $this->getCommunicationPanelFormat($record)
                            );
                        }
                        if ($ZadarmaSMSRecords->count() == $limit) {
                            $Since = $this->updateSinceDate($records->last(), $Since);
                        }
                    }
                }


                //Ringostat
                $ringostatProjectID = $currentDivision['miscs']['ringostat_project_id'] ?? '';
                $RingostatBuilder = EventAfterCall::where(function (Builder $q) use ($where) {
                    $q->where(function (Builder $query) use ($where) {
                        return $query->where('type', 'out')
                            ->where(function ($query) use ($where) {
                                foreach ($where['phones'] as $phone) {
                                    $query->orWhere('destination', 'LIKE', '%' . $phone);
                                }
                            });

                    })->orWhere(function ($query) use ($where) {
                        return $query->where('type', 'in')
                            ->where(function ($query) use ($where) {
                                foreach ($where['phones'] as $phone) {
                                    $query->orWhere('caller_number', 'LIKE', '%' . $phone);
                                }
                            });
                    });
                })->where('project_id', $ringostatProjectID);
                $Builder = (clone $RingostatBuilder)
                    ->where('call_timestamp', '>', $Since->getPreciseTimestamp(6))
                    ->where('call_timestamp', '<', $Till->getPreciseTimestamp(6))
                    ->orderBy('call_timestamp', 'DESC')
                    ->limit($limit);
                $ringostatRecords = $Builder->where(function (Builder $query) {
                    $ignoreList = CommunicationsIgnoreList::where('type', 'phones')->get(['value']);
                    return $query->where(function (Builder $q) use ($ignoreList) {
                        if ($ignoreList && $ignoreList->count() > 0) {
                            $q->whereNotIn('caller_number', $ignoreList->pluck('value')->toArray());
                        }
                    });
                })->get();

                if ($ringostatRecords->isNotEmpty()) {
                    foreach ($ringostatRecords as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record, $Timezone)
                        );
                    }
                    if ($ringostatRecords->count() == $limit) {
                        $Since = $this->updateSinceDate($records->last(), $Since);
                    }
                }


                //SMS
                $TwilioSmsBuilder = TwilioSms::where(function (Builder $q) use ($where) {
                    $q->where(function (Builder $query) use ($where) {
                        return $query->where('direction', 'outbound-api')
                            ->where(function ($query) use ($where) {
                                foreach ($where['phones'] as $phone) {
                                    $query->orWhere('to', 'LIKE', '%' . $phone);
                                }
                            });

                    })->orWhere(function ($query) use ($where) {
                        return $query->where('direction', 'inbound')
                            ->where(function ($query) use ($where) {
                                foreach ($where['phones'] as $phone) {
                                    $query->orWhere('from', 'LIKE', '%' . $phone);
                                }
                            });
                    });
                });
                $Builder = (clone $TwilioSmsBuilder)->where('created_at', '<', $Till->toDateTimeString())
                    ->where('created_at', '>', $Since->toDateTimeString())
                    ->with(['statuses' => function ($q) {
                        $q->orderBy('id', 'ASC');
                    }])
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit);
                $twilioRecords = $this->filterIgnoredTwilioRecords($Builder)->get();

                if ($twilioRecords->isNotEmpty()) {
                    foreach ($twilioRecords as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                    if ($twilioRecords->count() == $limit) {
                        $Since = $this->updateSinceDate($records->last(), $Since);
                    }
                }

            }


            // Gmail
            if (!empty($where['emails']) && 1 > 2) {
                $divisionMailboxes = Account::where('division_id', $currentDivision['id'])->where('active', 1)->get('id');
                $GmailBuilder = Message::searchByEmails($where['emails'])
                    ->when($divisionMailboxes->isNotEmpty(), function ($q) use ($divisionMailboxes) {
                        $q->whereIn('account_id', $divisionMailboxes->pluck('id')->toArray());
                    })
                    ->when($divisionMailboxes->isEmpty(), function ($q) {
                        $q->where('account_id', 0);
                    })->where(function (Builder $q) {
                        return $q->whereNotNull('miscs->to')->orWhereNotNull('miscs->from');
                    });

                $Builder = (clone $GmailBuilder)->where('created_at', '>', $Since)
                    ->where('created_at', '<', $Till)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit);

                $gmailRecords = $this->filterIgnoredGmailRecords($Builder)->get();
                if ($gmailRecords->isNotEmpty()) {
                    foreach ($gmailRecords as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record, $Timezone)
                        );
                    }
                    if ($gmailRecords->count() == $limit) {
                        $Since = $this->updateSinceDate($records->last(), $Since);
                    }
                }
            }

            // Client data
            if (isset($Client)) {
                $Orders = Order::with(['manager:id,name', 'manager.employee:id,name,l_name'])
                    ->where('division_id', session('division')['id'])
                    ->where('client_id', $Client->id)
                    ->whereBetween('created_at', [$Since, $Till])
                    ->limit($limit)
                    ->get();
                if ($Orders->isNotEmpty()) {
                    foreach ($Orders as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                    if ($Orders->count() == $limit) {
                        $Since = $this->updateSinceDate($records->last(), $Since);
                    }
                }
                // conversationMarks
                $ConversationMarks = ConversationMark::where('client_id', $Client->id)
                    ->whereBetween('created_at', [$Since, $Till])
                    ->where('type', 'read')
                    ->with('user', 'user.employee')
                    ->limit($limit)
                    ->get();
                if ($ConversationMarks->isNotEmpty()) {
                    foreach ($ConversationMarks as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                }
                // ClientActivity
                $ClientActivity = Client\Activity::where('client_id', $Client->id)
                    ->whereBetween('created_at', [$Since, $Till])
                    ->where('type', 'customer.inventory.save')
                    ->limit($limit)
                    ->get();
                if ($ClientActivity->isNotEmpty()) {
                    foreach ($ClientActivity as $record) {
                        $records->push(
                            $this->getCommunicationPanelFormat($record)
                        );
                    }
                }

            } elseif ($channelContactType) {
                // conversationMarks
                $ConversationMarks = ConversationMark::where('type', 'read')
                    ->where('contact_type', $channelContactType)
                    ->where('contact_value', 'like', '%' . $validated['contact']['channelContact'])
                    ->whereBetween('created_at', [$Since, $Till])
                    ->with('user', 'user.employee')
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


            // выкидываем все что раньше $Since
            $records = $records
                ->filter(function ($v) use ($Since) {
                    return $v->datetime >= $Since;
                })
                ->map(function ($v) {
                    $v->timestamp = $v->datetime->getTimestamp();
                    //$v->orderID = $this->detectOrder($v->item, $v->datetime);
//                $clientID = $this->detectClient($v->item);
//                $v->client = $clientID ? Client::first(['id', 'name', 'lname']) : null;
//                $v->channelContact = $this->getChannelContact($v->item);
                    return $v;
                })
                ->sortByDesc(function ($obj) {
                    return $obj->datetime;
                });

            // remove client dublicates
//            $ignoreList = ['clients' => []];

            /**
             * @var $LastDT CarbonInterface
             */
            $LastDT = $records->isNotEmpty() ? $records->last()->datetime : null;

            $response = [
                'success' => true,
                'untill' => $Since->getTimestamp(),
                'timezone' => $Timezone,
                'more' => $records->isNotEmpty() ?
                    $this->hasMoreRecords(
                        !empty($ZadarmaBuilder) ? $ZadarmaBuilder->
                        where('call_start', '<', $LastDT->setTimezone($Timezone)->toDateTimeString()) : null,
                        !empty($GmailBuilder) ? $GmailBuilder
                            ->where('updated_at', '<', $LastDT->toDateTimeString()) : null,
                        !empty($TwilioSmsBuilder) ? $TwilioSmsBuilder
                            ->where('created_at', '<', $LastDT->toDateTimeString()) : null,
                        !empty($SmsEventsBuilder) ? $SmsEventsBuilder
                            ->where('created_at', '<', $LastDT->toDateTimeString()) : null,
                        !empty($RingostatBuilder) ? $RingostatBuilder
                            ->where('call_timestamp', '<', $LastDT->getPreciseTimestamp(6)) : null,

                    ) : null,
//                'ignoreList' => $ignoreList,
                'records' => array_values($records->toArray())
            ];

        } catch (Exception $e) {
//            dump($e);
            $response['msg'] = $e->getMessage();
            $response['e'] = $e;
        }

        return response()
            ->json($response);
    }


    public function recordsAjax(Request $request): JsonResponse
    {
        $response = [
            'success' => false
        ];

        try {
            $validated = $request->validate([
                'filters.mode' => "nullable|in:all",
                'filters.contacts' => "nullable|in:all,myclients,unassigned",
                'filters.communications' => "nullable|in:all,unanswered",
                'filters.untill' => "nullable|int",
                'filters.ignoreList' => "nullable|array",
                'filters.channels' => "nullable|array",
                'filters.searchTerm' => "nullable|string",
                'filters.period' => "nullable|array",
                'filters.starred' => "in:all,starred,notstarred",
                'filters.responsible' => "nullable|array",
                'filters.responsible.*' => 'exists:App\Models\Employee,id'
            ]);
            $Till = $validated['filters']['untill'] ?
                CarbonImmutable::createFromTimestamp($validated['filters']['untill'],
                    'UTC') : CarbonImmutable::now('UTC');
            $Since = new CarbonImmutable('2021-01-01', 'UTC');
            if (!empty($validated['filters']['period']) && !empty($validated['filters']['period']['value'])) {
                if ($validated['filters']['period']['value'] == 'today') {
                    $Since = CarbonImmutable::now('UTC')->startOfDay();
                }
                if ($validated['filters']['period']['value'] == 'last7days') {
                    $Since = CarbonImmutable::now('UTC')->subDays(7)->startOfDay();
                }
                if ($validated['filters']['period']['value'] == 'last30days') {
                    $Since = CarbonImmutable::now('UTC')->subDays(30)->startOfDay();
                }
                if ($validated['filters']['period']['value'] == 'yesterday') {
                    $Since = CarbonImmutable::now('UTC')->subDays(1)->startOfDay();
                    if (empty($validated['filters']['untill'])) {
                        $Till = CarbonImmutable::now('UTC')->subDays(1)->endOfDay();
                    }
                }
            }
//            $Since = new CarbonImmutable('2023-01-23', 'UTC'); // test sandbox
//            $Till = new CarbonImmutable('2024-03-29', 'UTC'); // test sandbox

            $response = $this->contactsRecords($Since, $Till, $validated);

        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
            $response['exception'] = $e;
        }


        return response()
            ->json($response);
    }

    public function recordsUnanswered()
    {
        $Till = CarbonImmutable::now('UTC');
        $Since = new CarbonImmutable('2021-01-01', 'UTC');
//        $response = $this->records($Since, $Till, ['filters' => ['mode' => 'all', 'contacts' => 'all', 'communications' => 'unanswered']], 99999);

    }


    public function contactsRecords(CarbonInterface $Since, CarbonInterface $Till, $validated, $limit = 50)
    {
//        try {
        $records = collect([]);
        $currentDivision = session('division');

        $Timezone = !empty($currentDivision['miscs']['tz'])
            ? $currentDivision['miscs']['tz']
            : config('app.timezone');

        $ignoreList = !empty($validated['filters']['ignoreList'])
            ? $validated['filters']['ignoreList']
            : ['clients' => []]
        ;

        $includeOnlyClients = [];
        $findedClients = [];
        $includeFindedClients = [];
        $PBXController = new PBXController();
        $PBXController->hasZadarma();


        $starredRecords = null;
        if ($validated['filters']['starred'] != 'all') {
            $starredRecords = ConversationFavorites::where('user_id', Auth::id())->where('starred', 1)->get();
            $starredRecordsClients = $starredRecords->filter(fn($v) => $v->client_id);
            $starredRecordsContacts = $starredRecords->filter(fn($v) => !$v->client_id && $v->contact_type == 'phone');
            $starredRecordsEmails = $starredRecords->filter(fn($v) => !$v->client_id && $v->contact_type == 'email');
        }


        // if used searchTerm, search for customer
        if (!empty($validated['filters']['searchTerm'])) {
            $searchFindedClients = Client::newModelInstance()
                ->searchCustomerWithRequest(new Request(['interface' => 'orders', 'q' => $validated['filters']['searchTerm']]))->get();

//            dd($searchFindedClients);

            if ($searchFindedClients->isNotEmpty()) {
                $findedClients = Client::markFindedClientsEntities($searchFindedClients, Str::lower($validated['filters']['searchTerm']))->keyBy('key')->toArray();
            }
            // возможно єто номер лида (заказа)
            if (ctype_digit($validated['filters']['searchTerm'])) {
                $searchFindedClientsFromOrders = Order::where('client_id', '>', 0)
                    ->where('division_id', session()->get('division.id'))
                    ->where('id', $validated['filters']['searchTerm'])
                    ->get(['id', 'client_id']);

                if ($searchFindedClientsFromOrders->isNotEmpty()) {
                    $searchTerm = $validated['filters']['searchTerm'];
                    $searchFindedClientsFromOrders = $searchFindedClientsFromOrders->map(function ($O) use ($searchTerm) {
                        return ['key' => $O->client_id, 'finded' => 'Order #' . Str::replaceFirst($searchTerm, '<mark>' . $searchTerm . '</mark>', $O->id)];
                    })->keyBy('key')->toArray();
                    // merge
                    foreach ($searchFindedClientsFromOrders as $k => $v) {
                        $includeFindedClients[$k] = $v;
                        if (array_key_exists($k, $findedClients))
                            $findedClients[$k]['finded'] .= $v['finded'];
                        else
                            $findedClients[$k] = $v;
                    }
                }
            }
            // если ничего не нашли
//            if (!$includeFindedClients)
//                $includeFindedClients = [0];
        }

        if (!empty($validated['filters']['responsible'])) {
            $managers = Employee::whereIn('id', $validated['filters']['responsible'])->get(['id', 'auth_user_id']);
            $myClients = Order::whereHas('manager', function ($q) use ($managers) {
                return $q->whereIn('id', $managers->pluck('auth_user_id')->toArray());
            })->where('client_id', '>', 0)->groupBy('client_id')->get('client_id');
            if ($myClients->isNotEmpty()) {
                $includeOnlyClients = $myClients->pluck('client_id')->toArray();
                // search
                if (!empty($findedClients)) {
                    $includeOnlyClients = array_intersect(array_keys($findedClients), $includeOnlyClients);
                    if (empty($includeOnlyClients))
                        $includeOnlyClients = [0];
                }
            } else {
                $includeOnlyClients = [0];
            }
        }
        //
        if ($validated['filters']['contacts'] === 'myclients') {
            $myClients = Order::where('user_id', Auth::id())->where('client_id', '>',
                0)->groupBy('client_id')->get('client_id');
            if ($myClients->isNotEmpty()) {
                $includeOnlyClients = $myClients->pluck('client_id')->toArray();
                // search
                if (!empty($findedClients)) {
                    $includeOnlyClients = array_intersect(array_keys($findedClients), $includeOnlyClients);
                    if (empty($includeOnlyClients))
                        $includeOnlyClients = [0];
                }
            } else {
                $includeOnlyClients = [0];
            }
        }

        // Calls Ringostat
        $ringostatProjectID = $currentDivision['miscs']['ringostat_project_id'] ?? '';
        $ringostatBuilder = EventAfterCall::where('call_timestamp', '>', $Since->getPreciseTimestamp(6))
            ->where('call_timestamp', '<', $Till->getPreciseTimestamp(6))
            ->where('project_id', $ringostatProjectID)
            ->orderBy('call_timestamp', 'DESC')
            ->limit($limit);

        if (!empty($includeOnlyClients)) {
            $ringostatBuilder->searchByPhonesFromClients($includeOnlyClients);
        } elseif ($validated['filters']['starred'] == 'starred') {
            if ($starredRecords->isEmpty()) {
                $ringostatBuilder->where('id', 0);
            } else {
                $ringostatBuilder->where(function (Builder $q) use ($starredRecordsClients, $starredRecordsContacts) {
                    if ($starredRecordsClients->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($starredRecordsClients) {
                            $q->searchByPhonesFromClients($starredRecordsClients->pluck('client_id')->toArray());
                        });
                    }
                    if ($starredRecordsContacts->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($starredRecordsContacts) {
                            $q->searchByPhonesFromArray($starredRecordsContacts->pluck('contact_value')->toArray());
                        });
                    }
                });
            }
        }

        if (!empty($validated['filters']['searchTerm'])) {
            $ringostatBuilder->where(function (Builder $q) use ($validated) {
                $q->searchByCustomerPhone($validated['filters']['searchTerm']);
            });

            if (!empty($findedClients)) {
                $ringostatBuilder->orWhere(function ($q) use ($findedClients) {
                    $q->searchByPhonesFromClients(array_keys($findedClients));
                });
            }
        }

        if(isset($ignoreList['EventAfterCall'])){
            // убираем эл. со значением null (если есть), т.к. он ломает выборку
            $ignoreList['EventAfterCall'] = array_filter($ignoreList['EventAfterCall']);
        }

        $clientPhones = Client\Phone::whereIn('client_id', $ignoreList['clients'])
            ->get(['id', 'value'])
            ->pluck('value')
            ->toArray();
//            $clientPhones = [];

        // search by phone
        $ringostatBuilder->where(function (Builder $q) use ($ignoreList, $clientPhones) {
            // caller_id
            if (!empty($ignoreList['EventAfterCall']) || !empty($ignoreList['clients'])) {
                $q->where(function (Builder $q) use ($ignoreList, $clientPhones) {
                    if (!empty($ignoreList['EventAfterCall'])) {
                        $q->whereNotIn('caller_number', $ignoreList['EventAfterCall']);
                    }
                    if (!empty($ignoreList['clients'])) {
                        // change
//                        $clientPhones = Client\Phone::whereIn('client_id', $ignoreList['clients'])->get([
//                            'id', 'value'
//                        ])->pluck('value')->toArray();

                        if (!empty($clientPhones)) {
                            foreach ($clientPhones as $phone) {
                                $q->where('caller_number', 'NOT LIKE', "%{$phone}");
                            }
                        }
                    }
                });
            }
            //destination
            $q->where(function (Builder $q) use ($ignoreList, $clientPhones) {
                $q->whereNull('destination')->orWhereRaw('LENGTH(destination) > 5');
                if (!empty($ignoreList['EventAfterCall'])) {
                    $q->where(function ($q) use ($ignoreList) {
                        foreach ($ignoreList['EventAfterCall'] as $phone) {
                            $q->where('destination', 'NOT LIKE', "%{$phone}");
                        }
                    });
                }
                if (!empty($ignoreList['clients'])) {
                    $q->where(function ($q) use ($ignoreList, $clientPhones) {

                        // change
//                        $clientPhones = Client\Phone::whereIn('client_id', $ignoreList['clients'])->get([
//                            'id', 'value'
//                        ])->pluck('value')->toArray();

                        if (!empty($clientPhones)) {
                            foreach ($clientPhones as $phone) {
                                $q->where('caller_number', 'NOT LIKE', "%{$phone}");
                            }
                        }
                    });
                }
            });
        });

        // filter unanswered
        if ($validated['filters']['communications'] == 'unanswered') {
            // только входящие
            $ringostatBuilder->where('status', 'NO ANSWER')->where('type', '=', 'in');
        }

        if (empty($validated['filters']['channels']) || in_array('ringostat', $validated['filters']['channels'])) {
//            $ringostatBuilder = $this->filterIgnoredCallRecords($ringostatBuilder)->get();
            $ringostatRecords = $ringostatBuilder->where(function (Builder $query) {
                $ignoreList = CommunicationsIgnoreList::where('type', 'phones')->get(['value']);

                return $query->where(function (Builder $q) use ($ignoreList) {
                    if ($ignoreList && $ignoreList->count() > 0) {
                        $q->whereNotIn('caller_number', $ignoreList->pluck('value')->toArray());
                    }
                });
            })->get();

            if ($ringostatRecords->isNotEmpty()) {
                foreach ($ringostatRecords as $record) {
                    $records->push(
                        $this->getCommunicationPanelFormat($record, $Timezone)
                    );
                }
                if ($ringostatRecords->count() == $limit) {
                    $Since = $this->updateSinceDate($records->last(), $Since);
                }
            }
        } else {
            $ringostatBuilder = null;
        }

        // Calls Zadarma
        $zadarmaBuilder = CallsEvents::where('call_start', '>', $Since->setTimezone($Timezone)->toDateTimeString())
            ->where('call_start', '<', $Till->setTimezone($Timezone)->toDateTimeString())
            ->where('pbx_id', $currentDivision['miscs']['zadarma_pbx_id'])
            ->whereIn('event', ['NOTIFY_OUT_END', 'NOTIFY_END'])
//                ->when('')
//                ->where(function (Builder $q) {
//                    $q->where(function ($q) {
//                        $q->where('event', 'NOTIFY_OUT_END')->whereRaw('LENGTH(destination) > 5');
//                    })->orWhere('event', 'NOTIFY_END');
//                })
            ->orderBy('call_start', 'DESC')
            ->limit($limit);

        if (!empty($includeOnlyClients)) {
            $zadarmaBuilder->searchByPhonesFromClients($includeOnlyClients);
        } elseif ($validated['filters']['starred'] == 'starred') {

            if ($starredRecords->isEmpty()) {
                $zadarmaBuilder->where('id', 0);
            } else {
                $zadarmaBuilder->where(function (Builder $q) use ($starredRecordsClients, $starredRecordsContacts) {
                    if ($starredRecordsClients->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($starredRecordsClients) {
                            $q->searchByPhonesFromClients($starredRecordsClients->pluck('client_id')->toArray());
                        });
                    }
                    if ($starredRecordsContacts->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($starredRecordsContacts) {
                            $q->searchByPhonesFromArray($starredRecordsContacts->pluck('contact_value')->toArray());
                        });
                    }
                });

            }
        }


        if (!empty($validated['filters']['searchTerm'])) {
            $zadarmaBuilder->where(function (Builder $q) use ($validated) {
                $q->searchByCustomerPhone($validated['filters']['searchTerm']);
            });

            if (!empty($findedClients)) {
                $zadarmaBuilder->orWhere(function ($q) use ($findedClients) {
                    $q->searchByPhonesFromClients(array_keys($findedClients));
                });
            }
        }


        // search by phone
        $zadarmaBuilder->where(function (Builder $q) use ($ignoreList) {
            // caller_id
            if (!empty($ignoreList['CallsEvents']) || !empty($ignoreList['clients'])) {
                $q->where(function (Builder $q) use ($ignoreList) {
                    if (!empty($ignoreList['CallsEvents'])) {
                        $q->whereNotIn('caller_id', $ignoreList['CallsEvents']);
                    }
                    if (!empty($ignoreList['clients'])) {

                        // change
                        $clientPhones = Client\Phone::whereIn('client_id', $ignoreList['clients'])->get([
                            'id', 'value'
                        ])->pluck('value')->toArray();


                        if (!empty($clientPhones)) {
                            foreach ($clientPhones as $phone) {
                                $q->where('caller_id', 'NOT LIKE', "%{$phone}");
                            }
                        }
                    }
                });
            }
            //destination
            $q->where(function (Builder $q) use ($ignoreList) {
                $q->whereNull('destination')->orWhereRaw('LENGTH(destination) > 5');
                if (!empty($ignoreList['CallsEvents'])) {
                    $q->where(function ($q) use ($ignoreList) {
                        foreach ($ignoreList['CallsEvents'] as $phone) {
                            $q->where('destination', 'NOT LIKE', "%{$phone}");
                        }
                    });
                }
                if (!empty($ignoreList['clients'])) {
                    $q->where(function ($q) use ($ignoreList) {

                        // change
                        $clientPhones = Client\Phone::whereIn('client_id', $ignoreList['clients'])->get([
                            'id', 'value'
                        ])->pluck('value')->toArray();


                        if (!empty($clientPhones)) {
                            foreach ($clientPhones as $phone) {
                                $q->where('caller_id', 'NOT LIKE', "%{$phone}");
                            }
                        }
                    });
                }
            });
        });

        // filter unanswered
        if ($validated['filters']['communications'] == 'unanswered') {
            // только входящие
            $zadarmaBuilder->where('event', 'NOTIFY_END')->where('disposition', '!=', 'answered');
        }

        if (empty($validated['filters']['channels']) || in_array('zadarma', $validated['filters']['channels'])) {
            $zadarmaRecords = $this->filterIgnoredCallRecords($zadarmaBuilder)->get();
            if ($zadarmaRecords->isNotEmpty()) {
                foreach ($zadarmaRecords as $record) {
                    $records->push(
                        $this->getCommunicationPanelFormat($record, $Timezone)
                    );
                }
                if ($zadarmaRecords->count() == $limit) {
                    $Since = $this->updateSinceDate($records->last(), $Since);
                }
            }
        } else {
            $zadarmaBuilder = null;
        }

        /**
         * zadarma end
         */


//        $divisionMailboxes = Account::where('division_id', $currentDivision['id'])->where('active', 1)->get('id');
//        // Gmail
//        $gmailBuilder = Message::where('updated_at', '>', $Since)
//            ->when($divisionMailboxes->isNotEmpty(), function ($q) use ($divisionMailboxes) {
//                $q->whereIn('account_id', $divisionMailboxes->pluck('id')->toArray());
//            })
//            ->when($divisionMailboxes->isEmpty(), function ($q) {
//                $q->where('account_id', 0);
//            })
//            ->whereIn('tag', ['inbox', 'sent'])
//            ->where(function (Builder $q) {
//                return $q->whereNotNull('miscs->to')->orWhereNotNull('miscs->from');
////                return $q->where('miscs->to')->orWhere('miscs->from');
//            })
//            ->where('updated_at', '<', $Till)
//            ->orderBy('updated_at', 'DESC')
//            ->limit($limit);
//
//        if (!empty($includeOnlyClients)) {
//            $clientEmails = Client\Email::whereIn('client_id', $includeOnlyClients)->get(['id', 'value']);
//            if ($clientEmails->isNotEmpty()) {
//                $gmailBuilder->where(function (Builder $query) use ($clientEmails) {
//                    return $query->orWhere(function (Builder $q) use ($clientEmails) {
//                        return $q->searchByEmails($clientEmails->pluck('value')->toArray());
//                    });
//                });
//            } else {
//                $gmailBuilder->where('id', 0);
//            }
////                    ->pluck('value')->toArray();
//        } elseif ($validated['filters']['starred'] == 'starred') {
//
//            if ($starredRecords->isEmpty()) {
//                $gmailBuilder->where('id', 0);
//            } else {
//                $gmailBuilder->where(function (Builder $q) use ($starredRecordsClients, $starredRecordsEmails) {
//                    if ($starredRecordsClients->isNotEmpty()) {
//                        $q->orWhere(function ($q) use ($starredRecordsClients) {
//                            $q->searchByClients($starredRecordsClients->pluck('client_id')->toArray());
//                        });
//                    }
//                    if ($starredRecordsEmails->isNotEmpty()) {
//                        $q->orWhere(function ($q) use ($starredRecordsEmails) {
//                            $q->searchByEmails($starredRecordsEmails->pluck('contact_value')->toArray());
//                        });
//                    }
//                });
//
//            }
//        } elseif (!empty($validated['filters']['searchTerm'])) {
//            $gmailBuilder->searchByEmailLike($validated['filters']['searchTerm']);
//            if (!empty($includeFindedClients))
//                $gmailBuilder->orWhere(function ($q) use ($includeFindedClients) {
//                    $q->searchByClients(array_keys($includeFindedClients));
//                });
//        }
//
//
//        if (!empty($ignoreList['Message'])) {
//            $gmailBuilder->fromNotIn($ignoreList['Message'])->toNotIn($ignoreList['Message']);
//        }
//        if (!empty($ignoreList['clients'])) {
//            $clientEmails = Client\Email::whereIn('client_id', $ignoreList['clients'])->get([
//                'id', 'value'
//            ])->pluck('value')->toArray();
//            if (!empty($clientEmails)) {
//                $gmailBuilder->fromNotIn($clientEmails)->toNotIn($clientEmails);
//            }
//        }
//
//        // filter unanswered
//        if ($validated['filters']['communications'] == 'unanswered') {
//            // только входящие
//            $gmailBuilder->where('tag', 'inbox');
//        }
//
//        if (empty($validated['filters']['channels']) || in_array('gmail', $validated['filters']['channels'])) {
//            $gmailRecords = $this->filterIgnoredGmailRecords($gmailBuilder)->get();
//            if ($gmailRecords->isNotEmpty()) {
//                foreach ($gmailRecords as $record) {
//                    $records->push(
//                        $this->getCommunicationPanelFormat($record, $Timezone)
//                    );
//                }
//                if ($gmailRecords->count() == $limit) {
//                    $Since = $this->updateSinceDate($records->last(), $Since);
//                }
//            }
//        } else {
        $gmailBuilder = null;
//        }


        // Activity
        $ActivityBuilder = Client\Activity::whereBetween('created_at', [$Since, $Till])
            ->where('type', 'customer.inventory.save')
            ->whereJsonContains('miscs->division_id', 1)
            ->orderBy('created_at', 'DESC')
            ->limit($limit);

        if (!empty($includeOnlyClients)) {
            $ActivityBuilder->whereIn('client_id', $includeOnlyClients);
        }
        if (!empty($findedClients)) {
            $ActivityBuilder->whereIn('client_id', array_keys($findedClients));
        }
        if ($validated['filters']['starred'] == 'starred') {
            if ($starredRecords->isEmpty()) {
                $ActivityBuilder->where('id', 0);
            } else {
                if ($starredRecordsClients->isNotEmpty()) {
                    $ActivityBuilder->whereIn('client_id', $starredRecordsClients->pluck('client_id')->toArray());
                }
            }

        }
        if (!empty($ignoreList['clients'])) {
            $ActivityBuilder->whereNotIn('client_id', $ignoreList['clients']);
        }
        if (empty($validated['filters']['channels'])) {
            $ActivityRecords = $ActivityBuilder->get();
            if ($ActivityRecords->isNotEmpty()) {
                foreach ($ActivityRecords as $record) {
                    $records->push(
                        $this->getCommunicationPanelFormat($record)
                    );
                }
                if ($ActivityRecords->count() == $limit) {
                    $Since = $this->updateSinceDate($records->last(), $Since);
                }
            }
        } else {
            $ActivityBuilder = null;
        }

        //Zadarma SMS
        $zadarmaSmsBuiler = SmsEvents::whereBetween('created_at', [$Since, $Till])
            ->where('pbx_id', $PBXController->getPBXid());

        if (!empty($includeOnlyClients))
            $zadarmaSmsBuiler->searchByPhonesFromClients($includeOnlyClients);

        if (!empty($validated['filters']['searchTerm'])) {
            $zadarmaSmsBuiler->where(function (Builder $q) use ($validated) {
                $q->searchByCustomerPhone($validated['filters']['searchTerm']);
            });

            if (!empty($findedClients)) {
                $zadarmaSmsBuiler->orWhere(function ($q) use ($findedClients) {
                    $q->searchByPhonesFromClients(array_keys($findedClients));
                });
            }
        }


        if (empty($validated['filters']['channels']) || in_array('zadarma', $validated['filters']['channels'])) {
            $zadarmaRecords = $zadarmaSmsBuiler->orderBy('created_at', 'DESC')->limit($limit)->get();
            if ($zadarmaRecords->isNotEmpty()) {
                foreach ($zadarmaRecords as $record) {
                    $records->push(
                        $this->getCommunicationPanelFormat($record, $Timezone)
                    );
                }
                if ($zadarmaRecords->count() == $limit) {
                    $Since = $this->updateSinceDate($records->last(), $Since);
                }
            }
        } else {
            $zadarmaSmsBuiler = null;
        }


        //SMS
        $twilioSmsBuilder = TwilioSms::whereBetween('created_at', [$Since, $Till])
            ->where('division', $currentDivision['id'])
            ->orderBy('created_at', 'DESC')
            ->limit($limit);

        if (!empty($includeOnlyClients)) {
            $clientPhones = Client\Phone::whereIn('client_id', $includeOnlyClients)->get(['id', 'value']);
            if ($clientPhones->isNotEmpty()) {
                $twilioSmsBuilder->where(function ($q) use ($clientPhones) {
                    $q->where(function (Builder $q) use ($clientPhones) {
                        foreach ($clientPhones as $Phone) {
                            $q->orWhere('from', 'LIKE', '%' . $Phone->value);
                        }
                    })->orWhere(function (Builder $q) use ($clientPhones) {
                        foreach ($clientPhones as $Phone) {
                            $q->orWhere('to', 'LIKE', '%' . $Phone->value);
                        }
                    });
                });
            } else {
                $twilioSmsBuilder->where('id', 0);
            }
//                    ->pluck('value')->toArray();
        } elseif ($validated['filters']['starred'] == 'starred') {

            if ($starredRecords->isEmpty()) {
                $twilioSmsBuilder->where('id', 0);
            } else {
                $twilioSmsBuilder->where(function (Builder $q) use ($starredRecordsClients, $starredRecordsContacts) {
                    if ($starredRecordsClients->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($starredRecordsClients) {
                            $q->searchByClientPhones($starredRecordsClients->pluck('client_id')->toArray());
                        });
                    }
                    if ($starredRecordsContacts->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($starredRecordsContacts) {
                            $q->searchByPhonesFromArray($starredRecordsContacts->pluck('contact_value')->toArray());
                        });
                    }
                });

            }
        }


        if (!empty($validated['filters']['searchTerm'])) {
            $twilioSmsBuilder->where(function ($q) use ($validated, $findedClients) {
                $q->orWhere('from', 'LIKE', '%' . $validated['filters']['searchTerm'] . '%')
                    ->orWhere('to', 'LIKE', '%' . $validated['filters']['searchTerm'] . '%');

                if (!empty($findedClients)) {
                    $q->orWhere(function ($q) use ($findedClients) {
                        return $q->searchByClientPhones(array_keys($findedClients));
                    });
                }
            });
        }


        if (!empty($ignoreList['clients'])) {

            // change
            $clientPhones = Client\Phone::whereIn('client_id', $ignoreList['clients'])->get([
                'id', 'value'
            ])->pluck('value')->toArray();


            if (!empty($clientPhones)) {
                foreach ($clientPhones as $phone) {
                    $twilioSmsBuilder->where('from', 'NOT LIKE', "%{$phone}")->where('to', 'NOT LIKE', "%{$phone}");
                }
            }
        }

        // filter unanswered
        if ($validated['filters']['communications'] === 'unanswered') {
            // только входящие
            $twilioSmsBuilder->where('direction', 'inbound');
        }


        if (empty($validated['filters']['channels']) || in_array('twiliosms', $validated['filters']['channels'])) {
            $twilioSmsRecords = $this->filterIgnoredTwilioRecords($twilioSmsBuilder)->get();
            if ($twilioSmsRecords->isNotEmpty()) {
                foreach ($twilioSmsRecords as $record) {
                    $records->push(
                        $this->getCommunicationPanelFormat($record)
                    );
                }
                if ($twilioSmsRecords->count() == $limit) {
                    $Since = $this->updateSinceDate($records->last(), $Since);
                }
            }
        } else {
            $twilioSmsBuilder = null;
        }

        // remove all earlier $Since
        $records = $records->filter(function ($v, $k) use ($Since) {
            return $v->datetime >= $Since;
        })
            ->map(CommunicationsController::class . '::mapRecord')
            ->sortByDesc(function ($obj, $key) {
                return $obj->datetime;
            });
        // remove client dublicates

        $records = $records->filter(function ($v, $k) use (&$ignoreList, $validated) {
            if ($v->client) {
                if (in_array($v->client->id, $ignoreList['clients'])) {
                    return false;
                }
                $ignoreList['clients'][] = $v->client->id;

                if ($validated['filters']['contacts'] === 'unassigned') {
                    return false;
                }

                // check for earlier communication
                if ($validated['filters']['communications'] === 'unanswered') {
                    return !$this->clientHasCommunicationsLaterThan($v->client->id, $v->datetime);
                }

                return true;
            }
            $channelContact = $v->channelContact;
            // check for earlier communication
            if ($validated['filters']['communications'] === 'unanswered') {
                if (get_class($v->item) == TwilioSms::class || get_class($v->item) == CallsEvents::class || get_class($v->item) == EventAfterCall::class) {
                    if ($this->phoneHasCommunicationsLaterThan($channelContact, $v->datetime)) {
                        return false;
                    }
                } elseif (get_class($v->item) == Message::class) {
                    if ($this->emailHasCommunicationsLaterThan($channelContact, $v->datetime)) {
                        return false;
                    }
                }
            }

            if (array_key_exists($v->type, $ignoreList) && in_array($channelContact, $ignoreList[$v->type])) {
                return false;
            } else {
                if (get_class($v->item) == TwilioSms::class) {
                    $ignoreList['CallsEvents'][] = $channelContact;
                    return true;
                }
                $ignoreList[$v->type][] = $channelContact;
                return true;
            }
        });

        if (!empty($validated['filters']['searchTerm'])) {
            $records = $records->map(function ($item) use ($findedClients, $validated) {
                return self::setConversationRecordFindedBy($item, $findedClients, $validated['filters']['searchTerm']);
            });
        }

        // set hasNoAnswer (readed) param
        $records = $records->map(function ($item) {
            return self::isConversationRecordAnswered($item);
        });
        // $Since = из последней записи
        if ($records->isNotEmpty()) {
            $Since = $this->updateSinceDate($records->last(), $Since);
        }

        $hasMore = !empty($records->toArray()) ? $this->hasMoreRecords(
            $zadarmaBuilder ? $zadarmaBuilder->newQuery()->where('call_start', '<', $Since->setTimezone($Timezone)) : null,
            $gmailBuilder ? $gmailBuilder->newQuery()->where('updated_at', '<', $Since) : null,
            $twilioSmsBuilder ? $twilioSmsBuilder->newQuery()->where('created_at', '<', $Since) : null,
            $zadarmaSmsBuiler ? $zadarmaSmsBuiler->newQuery()->where('created_at', '<', $Since) : null,
            $ringostatBuilder ? $ringostatBuilder->newQuery()->where('call_timestamp', '<', $Since->getPreciseTimestamp()) : null,
        ) : false;

        $response = [
            'success' => true,
            'untill' => $Since->getTimestamp(),
            'untill_dt' => $Since->toDateTimeString(),
            'timezone' => $Timezone,
            'more' => $hasMore,
            'ignoreList' => $ignoreList,
            'records' => array_values($records->toArray())
        ];

        return $response;

//        } catch (Exception $e) {
//            $response['msg'] = $e->getMessage();
//        }
//
//        return response()
//            ->json($response);
    }

    private static function setConversationRecordFindedBy($record, $findedBy, $term)
    {
        if ($record->client && !empty($findedBy[$record->client->id])) {
            $record->findedByText = $findedBy[$record->client->id]['finded'];
        } elseif ($record->channelContact && Str::contains($record->channelContact, $term)) {
            $record->findedByText = 'Contact: ' . Str::replaceFirst($term, '<mark>' . $term . '</mark>', $record->channelContact);
        }
        return $record;
    }

    private static function isConversationRecordAnswered($record)
    {
        $isAnswered = false;
        if ($record->type == 'TwilioSms' && $record->item->direction == 'outbound-api') {
            $isAnswered = true;
        }
        if ($record->type == 'CallsEvents' &&
            (($record->item->event == 'NOTIFY_END' && $record->item->disposition == 'answered') || $record->item->event == 'NOTIFY_OUT_END')) {
            $isAnswered = true;
        }
        if ($record->type == 'EventAfterCall') {
            if (
                $record->item->type == 'out'
                || ($record->item->type == 'in' && $record->item->status != 'NO ANSWER')
            ){
                $isAnswered = true;
                if($record->item->type == 'in' && $record->item->status == 'VOICEMAIL') {
                    $isAnswered = false;
                }
            }

        }
        if ($record->type == 'Message' && $record->item->tag == 'sent') {
            $isAnswered = true;
        }
        if ($record->type == 'ConversationMark') {
            $isAnswered = true;
        }

        if (!$isAnswered) {
            // check for marks
            if ($record->client) {
                if (ConversationMark::where('client_id', $record->client->id)
                    ->where('type', 'read')
                    ->where('created_at', '>', $record->datetime)->count())
                    $isAnswered = true;
            } else {
                if ($record->type == 'CallsEvents' || $record->type == 'TwilioSms' || $record->type == 'EventAfterCall') {
                    if (ConversationMark::where('contact_type', 'phone')
                        ->where('type', 'read')
                        ->where('contact_value', 'like', '%' . $record->channelContact)
                        ->where('created_at', '>', $record->datetime)->count())
                        $isAnswered = true;
                }
                if ($record->type == 'Message') {
                    if (ConversationMark::where('contact_type', 'email')
                        ->where('type', 'read')
                        ->where('contact_value', $record->channelContact)
                        ->where('created_at', '>', $record->datetime)->count())
                        $isAnswered = true;
                }
            }
        }
        $record->isAnswered = $isAnswered;
        return $record;
    }

    /**
     * @param $clientID
     * @param $DT - UTC timezone
     * @return bool
     */
    private function clientHasCommunicationsLaterThan($clientID, $DT)
    {
        $ConversationMarks = ConversationMark::where('client_id', $clientID)
            ->where('type', '=', 'read')
            ->where('created_at', '>', $DT)
            ->count();
        if ($ConversationMarks)
            return true;

        $clientPhones = Client\Phone::where('client_id', $clientID)->get(['id', 'value']);
        if ($clientPhones->isNotEmpty()) {
            foreach ($clientPhones as $Phone) {
                if ($this->phoneHasCommunicationsLaterThan($Phone->value, $DT)) {
                    return true;
                }
            }
        }
        $clientEmails = Client\Email::where('client_id', $clientID)->get(['id', 'value']);
        if ($clientEmails->isNotEmpty()) {
            foreach ($clientEmails as $Email) {
                if ($this->emailHasCommunicationsLaterThan($Email->value, $DT)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $phone
     * @param $DT - UTC timezone
     * @return false
     */
    private function phoneHasCommunicationsLaterThan($phone, Carbon $DT)
    {
        $currentDivision = session('division');
        $Timezone = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('app.timezone');

        $ConversationMarks = ConversationMark::where('type', '=', 'read')
            ->where('contact_type', 'phone')
            ->where('contact_value', 'LIKE', '%' . $phone)
            ->where('created_at', '>', $DT)
            ->count();
        if ($ConversationMarks)
            return true;

        // Ringostat OUT
        $count = EventAfterCall::where('project_id', $currentDivision['miscs']['ringostat_project_id'])
            ->where('type', 'out')
            ->where('call_timestamp', '>', $DT->getPreciseTimestamp(6))
            ->where('destination', 'LIKE', "%{$phone}")
            ->where('status', '!=', 'NO ANSWER')
            ->count();
        if ($count) {
            return true;
        }
        // Ringostat IN
        $count = EventAfterCall::where('project_id', $currentDivision['miscs']['ringostat_project_id'])
            ->where('type', 'in')
            ->where('call_timestamp', '>', $DT->getPreciseTimestamp(6))
            ->where('caller_number', 'LIKE', "%{$phone}")
            ->where('status', '!=', 'NO ANSWER')
            ->count();
        if ($count) {
            return true;
        }

        // Zadarma OUT
        $count = CallsEvents::where('pbx_id', $currentDivision['miscs']['zadarma_pbx_id'])
            ->where('event', 'NOTIFY_OUT_END')
            ->where('call_start', '>', (clone $DT)->setTimezone($Timezone)->toDateTimeString())
            ->where('destination', 'LIKE', "%{$phone}")
            ->where('disposition', 'answered')
            ->count();
        if ($count) {
            return true;
        }
        // Zadarma IN
        $count = CallsEvents::where('pbx_id', $currentDivision['miscs']['zadarma_pbx_id'])
            ->where('event', 'NOTIFY_END')
            ->where('call_start', '>', (clone $DT)->setTimezone($Timezone)->toDateTimeString())
            ->where('caller_id', 'LIKE', "%{$phone}")
            ->where('disposition', 'answered')
            ->count();
        if ($count) {
            return true;
        }
        $count = TwilioSms::where('division', $currentDivision['id'])
            ->where('direction', 'outbound-api')
            ->where('created_at', '>', $DT->toDateTimeString())
            ->where('to', 'LIKE', "%{$phone}")
            ->count();
        if ($count) {
            return true;
        }
        return false;
    }

    private function emailHasCommunicationsLaterThan($email, $DT)
    {
        $ConversationMarks = ConversationMark::where('type', 'read')
            ->where('contact_type', 'email')
            ->where('contact_value', $email)
            ->where('created_at', '>', $DT)
            ->count();
        if ($ConversationMarks)
            return true;

        $count = Message::where('updated_at', '>', $DT)
            ->emailToIn([$email])
            ->count();
        if ($count) {
            return true;
        }
        return false;
    }


    public static function getChannelContact($Object)
    {
        if (get_class($Object) == CallsEvents::class) {
            if (Str::startsWith($Object->pbx_call_id, 'in_') && !empty($Object->caller_id)) {
                return Client\Phone::clearPhone($Object->caller_id);
            } elseif (Str::startsWith($Object->pbx_call_id, 'in_') && empty($Object->caller_id)) {
                return 'Anonymous-' . $Object->id;
            } elseif (Str::startsWith($Object->pbx_call_id, 'out_')) {
                $cleared = Client\Phone::clearPhone($Object->destination);
                if (strpos($cleared, '0002') === 0 || strpos($cleared, '0001') === 0) {
                    return Client\Phone::clearPhone(substr($cleared, 4));
                }
                if (strpos($cleared, '888') === 0) {
                    return Client\Phone::clearPhone(substr($cleared, 3));
                }
                return $cleared;
            }
        } elseif (get_class($Object) == EventAfterCall::class) {
            if ($Object->type == 'in') {
                return Client\Phone::clearPhone($Object->caller_number);
            } elseif ($Object->type == 'out') {
                return Client\Phone::clearPhone($Object->destination);
            }
        } elseif (get_class($Object) == TwilioSms::class) {
            if ($Object->direction == 'outbound-api') {
                return Client\Phone::clearPhone($Object->to);
            } elseif ($Object->direction == 'inbound') {
                return Client\Phone::clearPhone($Object->from);
            }
        } elseif (get_class($Object) == SmsEvents::class) {
            if ($Object->inbound) {
                return Client\Phone::clearPhone($Object->caller_id);
            } else {
                return Client\Phone::clearPhone($Object->caller_did);
            }
        } elseif (get_class($Object) == Message::class) {
            if (!empty($Object->miscs)) {
                if (!empty($Object->miscs['from'])) {
                    if (isset($Object->miscs['from']['email'])) {
                        return $Object->miscs['from']['email'];
                    }
                    if (isset($Object->miscs['from']['name'])) {
                        return $Object->miscs['from']['name'];
                    }
                }
                if (!empty($Object->miscs['to'])) {
                    $firstAddress = current($Object->miscs['to']);
                    if (isset($firstAddress['email'])) {
                        return $firstAddress['email'];
                    }
                }
            }
        } elseif (get_class($Object) == ConversationMark::class) {
            return $Object->contact_value;
        } elseif (get_class($Object) == Client\Activity::class) {
            return $Object->client_id;
        }

        throw new Exception('getChannelContact. Unknown record type: ' . $Object);
//        return null;
    }

    /**
     * @param $Object
     * @return Client|null
     * @throws Exception
     */
    public static function detectClient($Object)
    {
        $Client = null;
        if (get_class($Object) == ConversationMark::class && $Object->client_id) {
            return Client::find($Object->client_id);
        }
        if (get_class($Object) == Client\Activity::class && $Object->client_id) {
            return Client::find($Object->client_id);
        }
        $channelContact = self::getChannelContact($Object);
        if (
            (get_class($Object) == CallsEvents::class || get_class($Object) == TwilioSms::class ||
                get_class($Object) == SmsEvents::class || get_class($Object) == EventAfterCall::class ||
                (get_class($Object) == ConversationMark::class && $Object->contact_type == 'phone'))
            && $channelContact
            && strlen($channelContact) > 5
        ) {
            $Client = Client::whereHas('phones', function (Builder $q) use ($channelContact) {
                $q->where('value', 'LIKE', Client\Phone::clearPhone($channelContact));
            })->first();
        } elseif ((get_class($Object) == Message::class || (get_class($Object) == ConversationMark::class && $Object->contact_type == 'email')) && $channelContact) {
//            if (!empty($Object->miscs) && !empty($Object->miscs['from']) && filter_var($Object->miscs['from']['email'], FILTER_VALIDATE_EMAIL)) {
            $Client = Client::whereHas('emails', function (Builder $q) use ($channelContact) {
                $q->where('value', 'LIKE', '' . $channelContact . '');
            })->first();
        } elseif (get_class($Object) == ConversationMark::class) {
//            if()
//            $Client = Client::find()
        }
        return $Client ?? null;
    }

    /**
     * @param $Object
     * @return Client|null
     * @throws Exception
     */
    public static function detectClients($Object)
    {
        $Clients = null;
        $channelContact = self::getChannelContact($Object);
        if ((get_class($Object) == CallsEvents::class || get_class($Object) == TwilioSms::class) && $channelContact && strlen($channelContact) > 5) {
            $Clients = Client::whereHas('phones', function (Builder $q) use ($channelContact) {
                $q->where('value', 'LIKE', Client\Phone::clearPhone($channelContact));
            })->get();
        } elseif (get_class($Object) == Message::class && $channelContact) {
//            if (!empty($Object->miscs) && !empty($Object->miscs['from']) && filter_var($Object->miscs['from']['email'], FILTER_VALIDATE_EMAIL)) {
            $Clients = Client::whereHas('emails', function (Builder $q) use ($channelContact) {
                $q->where('value', 'LIKE', '' . $channelContact . '');
            })->get();
        }
        return $Clients ?? null;
    }

    public static function mapRecord($v, $setIsAnsweredAttribute = null)
    {
//                $v->timestamp = $v->datetime->getPreciseTimestamp(3);
        $v->timestamp = $v->datetime->getTimestamp();
        $v->client = self::detectClient($v->item);
        $v->collectionClients = self::detectClients($v->item);
        if ($v->client) {
            $v->client->load([
                'phones:id,client_id,value',
                'emails:id,client_id,value',
                'tags:id,title,color,icon',
                'notes:id,client_id,user_id,value',
                'notes.author:id,name'
            ]);
            $v->client->loadCount('orders');
        }
//        if ($v->collectionClients) {
//            $v->collectionClients = $v->collectionClients->map(function($client) {
//              return $client->load(['phones:id,client_id,value', 'emails:id,client_id,value']);
//            });
//        }
        $v->managers = $v->client
            ? Order::where('client_id', $v->client->id)
                ->orderBy('id', 'DESC')
                ->groupBy('user_id')
                ->get(['user_id'])
                ->pluck('user_id')
            : [];
        $v->managerAbbr = !empty($v->managers)
            ? User::whereIn('id', $v->managers)
                ->get(['name'])
                ->pluck('name')
                ->first()
            : null;
        $v->channelContact = (string)self::getChannelContact($v->item);
        $v->starred = self::isStarredContact($v);

        if ($setIsAnsweredAttribute) {
            return self::isConversationRecordAnswered($v);
        }

        return $v;
    }


    protected static function isStarredContact($v)
    {
        $MarkRecord = null;
        if ($v->client) {
            $MarkRecord = ConversationFavorites::where('client_id', $v->client->id)
                ->where('user_id', Auth::id())
                ->first();
        } elseif (
            (get_class($v->item) == CallsEvents::class || get_class($v->item) == TwilioSms::class)
        ) {
            $MarkRecord = ConversationFavorites::where('contact_type', 'phone')
                ->where('contact_value', $v->channelContact)
                ->where('user_id', Auth::id())
                ->first();
        } elseif (get_class($v->item) == Message::class) {
            $MarkRecord = ConversationFavorites::where('contact_type', 'email')
                ->where('contact_value', $v->channelContact)
                ->where('user_id', Auth::id())
                ->first();
        }
        if ($MarkRecord)
            return !empty($MarkRecord->starred);

        return false;
    }


    private function detectOrder($clientID, Carbon $EventTime)
    {
        //
//        if ($clientID = $this->detectClient($Object)) {
        // ищем заказ, который создан или активен в период коммуникации?
        $Orders = Order::where('client_id', $clientID)->get();
        if ($Orders && $Orders->count() > 0) {
//                if($Orders->count() == 1) {
            return $Orders->last()->id;
//                }
        }
//        }
        return null;
    }

    private function hasMoreRecords(...$Builders)
    {
        foreach ($Builders as $Builder) {
            if ($Builder && $Builder->count()) {
                return true;
            }
        }
        return false;
    }

    private function filterIgnoredGmailRecords(Builder $Builder)
    {
        $ignoreList = CommunicationsIgnoreList::where('type', 'emails')->get(['value']);
        $Builder->with(['data', 'account:id,miscs']);
//            ->where('tag', 'inbox');
        if ($ignoreList && $ignoreList->count() > 0) {
            $Builder->fromNotIn($ignoreList->pluck('value')->toArray());
            $Builder->toNotIn($ignoreList->pluck('value')->toArray());
        }

        return $Builder;
    }

    private function filterIgnoredTwilioRecords(Builder $Builder)
    {
        $ignoreList = CommunicationsIgnoreList::where('type', 'phones')->get(['value']);

        //$Builder->with(['data', 'account:id,miscs'])
        if ($ignoreList && $ignoreList->count() > 0) {
            foreach ($ignoreList->pluck('value')->toArray() as $phone) {
                $Builder->where('from', 'NOT LIKE', "%{$phone}")->where('to', 'NOT LIKE', "%{$phone}");
            }
        }
//        $Collection = $Builder->get();
//        if($Collection->isNotEmpty()) {
//
//        }
        return $Builder;
    }


    /**
     *
     * @param $direction
     * @param $limit
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function filterIgnoredCallRecords(Builder $Builder)
    {
        $ignoreList = CommunicationsIgnoreList::where('type', 'phones')->get(['value']);

        return $Builder->with('internalEmployee:id,name,l_name')
            ->where(function (Builder $q) use ($ignoreList) {
                if ($ignoreList && $ignoreList->count() > 0) {
                    $q->whereNotIn('caller_id', $ignoreList->pluck('value')->toArray());
                }
            });
    }
}
