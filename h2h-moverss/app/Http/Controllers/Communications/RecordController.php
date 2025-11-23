<?php

namespace App\Http\Controllers\Communications;

use App\Enums\Communications\Filter\EntityEnum;
use App\Enums\Communications\Filter\PeriodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Communications\FlowRecordFilterRequest;
use App\Http\Requests\Communications\RecordFilterRequest;
use App\Http\Requests\Communications\RecordForOrderRequest;
use App\Models\Client\Activity;
use App\Models\Communications\CallInfo;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationMark;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Tasks\Task;
use App\Models\Twilio\TwilioSms;
use App\Models\Vapi;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use App\Services\Communications\FormatterService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RecordController extends Controller
{
    protected CarbonImmutable $since;
    protected $currentDivision;
    protected $tz;

    public function __construct(protected FormatterService $formatterService)
    {
        $this->since = new CarbonImmutable('2021-01-01', 'UTC');

        $this->currentDivision = session('division');

        $this->tz = isset($this->currentDivision['miscs']['tz']) && !empty($this->currentDivision['miscs']['tz'])
            ? $this->currentDivision['miscs']['tz']
            : config('app.timezone')
        ;
    }

    /**
     * test @see \Tests\Feature\Communications\Record\IndexTest
     */
    public function index(RecordFilterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $limit = $validated['per_page'] ?? 30;
            $offset = $validated['page'] ?? 1;
            $offsetResponse = $offset;
            if ($offset == 1) $offset = 0;

            $filters = $validated['filters'] ?? [];
            $ignore = $filters['ignoreList'] ?? [];

            $searchTerm = $filters['searchTerm'] ?? null;

            $currentDivision = session('division');

            if(in_array(CallsEvents::MORPH_NAME, $filters['channels'])){
                $filters['channels'][] = SmsEvents::MORPH_NAME;
            }
            if(empty($filters['channels'])){
                $filters['channels'] = [
                    TwilioSms::MORPH_NAME,
                    EventAfterCall::MORPH_NAME,
                    CallsEvents::MORPH_NAME,
                    SmsEvents::MORPH_NAME,
                    Activity::MORPH_NAME,
                    Message::MORPH_NAME,
                    Vapi\CallEvent::MORPH_NAME
                ];
            }

            $tz = !empty($currentDivision['miscs']['tz'])
                ? $currentDivision['miscs']['tz']
                : config('app.timezone');

            $recsPaginator = CommunicationRecord::query()
                ->filter($filters)
                ->with([
                    'entity',
                    'client'
                ])
                ->where('division_id', $currentDivision['id'])
                ->orderBy('sort_at', 'DESC')
                ->paginate(
                    perPage: $limit,
                    page: $offset,
                )
            ;

            $hasMore = $recsPaginator->hasMorePages();

            $recs = collect($recsPaginator->items());

            $tmpId = [];

            foreach ($recs as $rec) {
                $target = $rec->client_id ?: $rec->channel_contact;
                if(isset($ignore[$target])){
                    continue;
                }
                $ignore[$target] = $target;
                $tmpId[] = $rec->id;
            }

            $recsFiltered = $recs->filter(function (CommunicationRecord $rec) use ($tmpId, $tz, $searchTerm) {
                return in_array($rec->id, $tmpId);
            });

            $recsResult = $recsFiltered->map(function (CommunicationRecord $item) use ($tz, $searchTerm) {
                return $this->formatterService->recForMainPanel($item, $tz, $searchTerm);
            })
            ;

        } catch (\Throwable $e) {
            \Log::error('CommunicationRecordController@index FAIL', [$e]);
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'timezone' => $tz,
            'more' => $hasMore,
            'page' => $offsetResponse,
            'records' => $recsResult
                ->reject(function ($item) {
                    return empty($item);
                })
                ->values()
                ->toArray(),
            'ignore' => $ignore,
        ]);
    }

    /**
     * test @see \Tests\Feature\Communications\Record\FlowTest
     */
    public function flow(FlowRecordFilterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $limit = $validated['per_page'] ?? 18;
            // TODO есть проблема с подгрузкой данных если их больше лимит (поэтому увеличен), нужно подумать над выборкой, чтоб из начально подтягивать новые записи
//            $limit = $validated['per_page'] ?? 150;
            $offset = $validated['page'] ?? 1;
            $offsetResponse = $offset;
            if ($offset == 1) $offset = 0;

            $currentDivision = session('division');
            $tz = !empty($currentDivision['miscs']['tz'])
                ? $currentDivision['miscs']['tz']
                : config('app.timezone');

            $recsPaginator = $this->getFlowRecPagination(
                filter: $validated,
                limit: $limit,
                offset: $offset,
            );

            $hasMore = $recsPaginator->hasMorePages();

            $recs = collect($recsPaginator->items());

            $recsResult = $recs->map(function (CommunicationRecord $item) use ($tz) {
                return $this->formatterService->recForMainPanelBase($item, $tz);
            })
            ;

            $meta = [];
            $channelContact = $validated['contact']['channelContact'] ?? null;
            $callInfo = CallInfo::query()->where('channel_contact', $channelContact)->get();
            if($callInfo->count() > 0){
                $sum = $callInfo->sum('score');
                $meta['callInfo'] = [
                    'count' => $callInfo->count(),
                    'avg' => round($sum/$callInfo->count(), 2),
                    'data' => $callInfo->toArray(),
                ];
            }
        } catch (\Throwable $e) {
            \Log::error('CommunicationRecordController@index FAIL', [$e]);
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return response()
            ->json([
                'success' => true,
                'more' => $hasMore,
                'page' => $offsetResponse,
                'timezone' => $tz,
                'meta' => $meta,
                'records' => $recsResult
                    ->reject(function ($item) {
                        return empty($item);
                    })
                    ->values()
                    ->toArray()
            ]);
    }

    public function getFlowRecPagination(
        array $filter,
        $limit,
        $offset
    ): LengthAwarePaginator
    {
        return CommunicationRecord::query()
            ->whereNotIn('entity_type',[
                Order\Notes::MORPH_NAME,
                Task::MORPH_NAME,
            ])
            ->when(
                !is_null($filter['contact']['client']), function(Builder $b) use ($filter) {
                $phones = [];
                foreach($filter['contact']['client']['phones'] ?? [] as $phone){
                    $phones[] = $phone['value'];
                }

                if(empty($phones)){
                    $b->where('client_id', $filter['contact']['client']['id'])
                    ;
                } else {
                    $b->where('client_id', $filter['contact']['client']['id'])
                        ->orWhereIn('channel_contact', $phones)
                    ;
                }
            })
            ->when(
                is_null($filter['contact']['client']), function(Builder $b) use ($filter) {
                $b->where('channel_contact', $filter['contact']['channelContact']);
            }
            )
            ->orderBy('sort_at', 'DESC')
            ->paginate(
                perPage: $limit,
                page: $offset,
            )
        ;
    }

    /**
     * test @see \Tests\Feature\Communications\Record\ForOrderTest
     */
    public function forOrder(RecordForOrderRequest $request)
    {
        try {
            $validated = $request->validated();

            $order = Order::query()
                ->with([
                    'client',
                    'client.phones',
                    'client.emails',
                    'pinnedNotes',
                    'pinnedNotes.communicationRecord',
                    'pinnedNotes.author:id,name'
                ])
                ->where('id', $validated['orderID'])
                ->first()
            ;



            $dateFrom = (new CarbonImmutable($order->created_at, config('app.timezone')))
                ->modify("-2 day midnight")->setTimezone('UTC');

            $dateTo = CarbonImmutable::now('UTC')->addDay();
            // Заказ со статусом закрыто
            // todo зарефакторить статусы
//            if (in_array($order->status_id, [9, 10, 12, 13, 14, 19])) {
//                $dateTo = $order->updated_at->modify('+3 days midnight');
//            }

            $tz = config('app.timezone');
            if ($order->division_id) {
                $division = Division::findOrFail($order->division_id);
                if (!empty($division->miscs['tz'])) {
                    $tz = $division->miscs['tz'];
                }
            }
            $channelContacts = null;
            $recsQuery = CommunicationRecord::query()
                ->with([
                    'entity',
                    'client',
                    'client.phones',
                    'client.emails'
                ])
                ->whereNotIn('entity_type', [
                    Order::MORPH_NAME
                ])
                ->whereBetween('sort_at', [$dateFrom, $dateTo])
                ->where(function (Builder $query) use ($order, &$channelContacts) {
                    $q = $query->where('order_id', $order->id);

                    if($order->client){
                        $contacts = [];
                        foreach($order->client->phones as $phone){
                            /** $var $phone Phone */
                            $contacts[] = $phone->value;
                            $channelContacts = $phone->value;
                        }
                        foreach($order->client->emails as $email){
                            /** $var $email Email */
                            $contacts[] = $email->value;
                        }

                        if(!empty($contacts)){
                            $q->orWhereIn('channel_contact', $contacts);
                        }

                        $q->orWhere(function ($query) use ($order) {
                            $query->where('entity_type', ConversationMark::MORPH_NAME)
                                ->where('client_id', $order->client->id);
                        });
                    }

                    return $q;
                })
                ->orderBy('sort_at', 'DESC')
            ;

            $recs = $recsQuery
                ->get();

            $recsResult = $recs
                ->map(function (CommunicationRecord $item) use ($tz) {
                    return $this->formatterService->recForMainPanelBase($item, $tz);
                })
            ;

            $pinedNotes = $order
                ->pinnedNotes
                ->map((function (Order\Notes $notes) {
                    if(is_null($notes->communicationRecord)){
                        return null;
                    }

                    return $this->formatterService
                        ->recForMainPanelBase($notes->communicationRecord);
                }));

            $meta = [];
            if($channelContacts){
                $callInfo = CallInfo::query()->where('channel_contact', $channelContacts)->get();
                if($callInfo->count() > 0){
                    $sum = $callInfo->sum('score');
                    $meta['callInfo'] = [
                        'count' => $callInfo->count(),
                        'avg' => round($sum/$callInfo->count(), 2),
                        'data' => $callInfo->toArray(),
                    ];
                }
            }

        } catch (\Throwable $e) {
            \Log::error('CommunicationRecordController@index FAIL', [$e]);
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'data' => [
                'pinnedNotes' => $pinedNotes
                    ->reject(function ($item) {
                        return is_null($item);
                    }),
                'records' => $recsResult
                    ->reject(function ($item) {
                        return empty($item);
                    })
                    ->values()
                    ->toArray(),
                'meta' => $meta,
                'recordsTill' => $dateFrom->getTimestamp(),
                'dateFrom' => $dateFrom,
                'dateTill' => $dateTo,
                'more' => false,
//                'more' => $hasMore,
            ]
        ]);
    }

    public function emailData(Request $request, $id)
    {
        try {
            $model = Message::query()
                ->with(['data'])
                ->where('id', $id)
                ->first()
            ;

        } catch (\Throwable $e) {
            \Log::error('CommunicationRecordController@emailData FAIL', [
                'input' => ['id' => $id],
                'error' => $e
            ]);
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'data' => $model
        ]);
    }

    /**
     * test @see \Tests\Feature\Communications\Record\DataForFilterTest
     */
    public function dataForFilter(Request $request): JsonResponse
    {
        $data = [
            'channels' => [
                Message::MORPH_NAME        => 'Emails',
                TwilioSms::MORPH_NAME      => 'Twilio SMS',
                CallsEvents::MORPH_NAME    => 'ZadarmaPBX',
                EventAfterCall::MORPH_NAME => 'RingostatPBX'
            ],
            'period' => [
                PeriodEnum::Today->value        => 'Today',
                PeriodEnum::Yesterday->value    => 'Yesterday',
                PeriodEnum::Last_7_days->value  => 'Last 7 days',
                PeriodEnum::Last_30_days->value => 'Last 30 days',
                PeriodEnum::Any->value          => 'Any'
            ],
            'entities' => [
                EntityEnum::All->value    => 'All',
                EntityEnum::Calls->value  => 'Text & Calls',
                EntityEnum::Emails->value => 'Emails'
            ]
        ];

        return $this->responseDataJson($data);
    }
}
