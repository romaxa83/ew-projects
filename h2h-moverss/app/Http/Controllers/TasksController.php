<?php

namespace App\Http\Controllers;

use App\Http\Requests\TasksRequest;
use App\Services\Communications\FormatterService;
use App\Services\Communications\RecordCreateService;
use App\Services\Communications\RecordRemoveService;
use Carbon\CarbonImmutable;
use App\Models\{Client, Tasks\Status, Tasks\Subscriber, Tasks\Task, Tasks\Type};
use App\Traits\ResponseFormatter;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\{JsonResponse, Request};

/**
 * App Tasks.
 */
class TasksController extends Controller
{
    use ResponseFormatter;

    public function __construct(public FormatterService $formatterService)
    {}

    /**
     * Load user Tasks + statistics.
     * @param Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function session(Task $task, Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'isInit' => 'required|boolean',
            'status' => 'required|numeric',
            'user' => 'nullable|numeric',
            'dateFrom' => 'required|string|date_format:"Y-m-d"',
            'dateTo' => 'required|string|date_format:"Y-m-d"',
        ]);


        $records = $task->byFilters($validatedData)
            ->with([
                'subscribers',
                'author:id,name,email',
                'executor:id,name,email'
            ])
            ->get();

        $records = $this->formatRecords($records);

        $resp = [
            'success' => true,
            'records' => $records,
        ];

        if ($request->get('isInit')) {
            $resp = array_merge($resp, [
                'whoami' => [
                    'uid' => Auth::id(),
                    'is_multiple' => $request->session()->get('division.is_multiple'),
                    'is_admin' => Auth::user()->isAdmin(),
                ],
                'additional' => [
                    'statuses' => Status::active()->get()->keyBy('id'),
                    'types' => Type::get()->keyBy('id'),
                    'users' => User::get(['id', 'name', 'active'])->keyBy('id'),
                ]
            ]);
        }

        return response()
            ->json($resp);
    }

    /**
     * Get tasks Pipeline format
     * @return JsonResponse
     */
    public function pipeline(Request $request)
    {
//        dump(Carbon::today()->endOfWeek());
//        dump(session('division'));
        try {
            $divisionID = $request->session()->get('division.id');

            $validated = $request->validate([
                'manager' => 'array',
                'status' => 'in:inwork,all,completed',
            ]);

            $records = [];
            $Tasks = Task::where(function (Builder $q) use ($divisionID) {
                $q->whereNull('division_id')->orWhere('division_id', $divisionID);
            })->where(function (Builder $q) {
                return $q->whereBetween('due_date', [Carbon::today()->startOfDay(), Carbon::today()->endOfWeek()])
                    ->orWhere('due_date', '<=', Carbon::now());
            })->where(function (Builder $q) use ($validated) {
                if (!empty($validated['manager']))
                    $q->whereIn('executor_id', $validated['manager']);
                if ($validated['status'] == 'inwork')
                    $q->where('status_id', '<>', 3);
                if ($validated['status'] == 'completed')
                    $q->where('status_id', '=', 3);
                return $q;
            })->with([
                'type',
                'order:id,client_id',
                'order.client:id,name,lname',
                'author.employee:id,name,l_name',
                'executor.employee:id,name,l_name'
            ])
                ->take(500)
                ->get();

            if ($Tasks->count()) {
                foreach ($Tasks as $Task)
                    $records[] = $this->getCommunicationPanelFormat($Task);
            }
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'exception' => $e,
                ]);
        }
        return response()
            ->json([
                'success' => true,
                'records' => $records,
                'whoami' => [
                    'uid' => Auth::id(),
//                    'is_multiple' => $request->session()->get('division.is_multiple'),
                    'is_admin' => Auth::user()->isAdmin(),
                ],
                'additional' => [
                    'statuses' => Status::active()->get()->keyBy('id'),
                    'types' => Type::get()->keyBy('id'),
                    'users' => User::get(['id', 'name', 'active'])->keyBy('id'),
                ],
                'timezone' => !empty(session('division')['miscs']['tz']) ? session('division')['miscs']['tz'] : 'utc'
            ]);
    }

    public function environment(Request $request)
    {
        try {
            $whoami = [
                'uid' => Auth::id(),
//                    'is_multiple' => $request->session()->get('division.is_multiple'),
                'is_admin' => Auth::user()->isAdmin(),
            ];
            $additional = [
                'statuses' => Status::active()->get()->keyBy('id'),
                'types' => Type::get()->keyBy('id'),
                // admin, manager, estimator
                'users' => User::whereHas('roles', function ($q) {
                    $q->whereIn('role_id', [1, 5, 8]);
                })
                    ->whereJsonContains('division_ids', $request->session()->get('division.id'))
                    ->get(['id', 'name', 'active'])->keyBy('id'),
            ];
            $timezone = !empty(session('division')['miscs']['tz']) ? session('division')['miscs']['tz'] : 'utc';

        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'exception' => $e,
                ]);
        }
        return response()
            ->json([
                'success' => true,
                'whoami' => $whoami,
                'additional' => $additional,
                'timezone' => $timezone
            ]);
    }


    /**
     * Read Task data.
     * @param Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Task $task, Request $request): JsonResponse
    {
        $record = $task
            ->with([
                'subscribers',
                'author:id,name,email',
                'executor:id,name,email',
                'status:id,title,class'
            ])
            ->findOrFail((int)$request->id);

        return response()
            ->json([
                'success' => true,
                'record' => $record,
            ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $user = auth()->user();
        $tz = $request->session()->get('division.miscs.tz');
//        $tz = "America/Chicago";

        $start = CarbonImmutable::now($tz)->startOfDay()->setTimezone('UTC');
        $end = CarbonImmutable::now($tz)->endOfDay()->setTimezone('UTC');

        $queryBuilder = Task::query()
            ->where('executor_id', $user->id)
        ;

        return response()
            ->json([
                'success' => true,
                'data' => [
                    'for_today' => $queryBuilder
                        ->clone()
                        ->where('status_id', Status::IN_WORK_ID)
                        ->whereBetween('due_date', [$start, $end])
                        ->count(),
                    'opened' => $queryBuilder
                        ->clone()
                        ->where('status_id', Status::IN_WORK_ID)
                        ->count(),
                    'overdue' => $queryBuilder
                        ->clone()
                        ->where('status_id', Status::IN_WORK_ID)
                        ->where('due_date', '<', $start)
                        ->count(),
                ],
            ]);
    }

    public function viewedAll(): JsonResponse
    {
        $user = auth()->user();
        if($user){
            Task::query()
                ->where('executor_id', $user->id)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
        }

        return response()
            ->json(['success' => true]);
    }

    /**
     * Create new Task.
     * @param TasksRequest $request
     * @param Task $task
     * @return JsonResponse
     * @throws \Throwable
     *
     * test @see \Tests\Feature\Tasks\Task\CreateTest (дополнить тесты)
     */
    public function create(TasksRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        $task->fill($validated['record']);
        $task->user_id = Auth::id();
        $task->is_read = false;
        if (empty($task->division_id))
            $task->division_id = session('division')['id'];

        $divisionMiscs = $request->session()->get('division.miscs');

        $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $validated['record']['due_date'], $divisionMiscs['tz'])
            ->setTimezone('UTC');

        $task->due_date = $dueDate;

        if ($miscs = $validated['record']['miscs']) {
            $miscs['original'] = [
                'due_date' => $task->due_date ?? null,
            ];
            $miscs['relation']['branch_id'] = $miscs['relation']['branch_id'] ?? $request->session()->get('division.id');
            $task->miscs = $miscs;
        }

        DB::transaction(function () use (&$task, $validated) {
            $task->status_id = 1;
            $task->save();

            $subscribers = [];
            foreach ($validated['record']['subscribers'] as $user_id) {
                $subscribers[] = new Subscriber(['user_id' => $user_id]);
            }
            $task->subscribers()->saveMany($subscribers);
            $task->history()->create([
                'prev_status' => 1,
                'new_status' => 1,
                'user_id' => Auth::id(),
                'created_at' => now()->toDateTimeString()
            ]);
        });

        $task->refresh();

        $rec = RecordCreateService::handler($task);

        $task->load([
            'subscribers',
            'author:id,name,email',
            'executor:id,name,email'
        ]);
        if (!empty($validated['returnFormat']) && $validated['returnFormat'] == 'communicationPanel') {
            $task->load([
                'type', 'author.employee:id,name,l_name', 'executor.employee:id,name,l_name'
            ]);
            return response()
                ->json([
                    'success' => true,
                    'record' => $this->formatterService->recForMainPanelBase($rec),
                ]);
        }
        return response()
            ->json([
                'success' => true,
                'record' => $this->formatRecord($task),
            ]);
    }

    /**
     * test @see \Tests\Feature\Tasks\Task\RemoveTest
     */
    public function remove(Request $request)
    {
        try {
            $response = ['success' => false];
            $validatedData = $request->validate([
                'id' => 'required|exists:tasks',
            ]);
            $Task = Task::find($validatedData['id']);

            if ($Task->user_id != Auth::user()->id && !Auth::user()->isAdmin()) {
                throw new Exception('You have no permissions to delete other users tasks!');
            }

            // todo refactoring
            if ($Task->status_id == 3)
                throw new Exception('This task is completed!');

            RecordRemoveService::handler($Task);

            $Task->delete();
            $response = ['success' => true];

        } catch (Exception $e) {
            $response ['msg'] = $e->getMessage() .
                (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '');

        }
        return response()
            ->json($response);
    }


    /**
     * Complete a task with result text
     * or recreate a task.
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     * @throws Exception
     */
    public function modify(Request $request, Task $task): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'id' => 'required|exists:tasks',
                'mode' => 'required|string|max:15',
                'val' => 'required|numeric',
                'result' => 'nullable|string|max:250',
                'reCreate.type_id' => 'nullable|integer|exists:tasks_types,id',
                'reCreate.due_date' => 'required_with:reCreate|string|date_format:"Y-m-d H:i:s"',
                'reCreate.executor_id' => 'required_with:reCreate|exists:App\User,id',
                'returnFormat' => 'nullable|string',
                'orderID' => 'nullable|exists:orders,id',
            ]);

            $task = $task->find($validatedData['id']);
            // TODO Need parametr to division!
//            if ((int)$task->executor_id !== Auth::id() &&
//                (int)$task->user_id !== Auth::id() &&
//                !Auth::user()->isAdmin()) {
//                throw new Exception('You are not holder of this task');
//            }

            if ($validatedData['mode'] === 'status') {
                $task->history()->create([
                    'prev_status' => $task->status_id,
                    'new_status' => $validatedData['val'],
                    'user_id' => Auth::id(),
                    'created_at' => now()->toDateTimeString()
                ]);

                $task->status_id = $validatedData['val'];
                if ($validatedData['result']) {
                    $task->result = strip_tags($validatedData['result']);
                }
            } elseif ($validatedData['mode'] === 'delay') {
                $task->due_date = $task->due_date->addMinutes($validatedData['val']);
            }

            $task->save();

            $task->load([
                'subscribers',
                'author:id,name,email',
                'executor:id,name,email',
                'type',
                'author.employee:id,name,l_name',
                'executor.employee:id,name,l_name'
            ]);

            // Recreate new task
            if (!empty($validatedData['reCreate']['due_date'])) {
                $new = $task->replicate();
                $timezone = !empty(session('division')['miscs']['tz']) ? session('division')['miscs']['tz'] : 'utc';

//                (new Carbon($validatedData['reCreate']['due_date'], $timezone))->setTimezone('UTC');
                if ($validatedData['result']) {
                    $new->description = strip_tags($validatedData['result']);
                }
                $new->due_date = (new Carbon($validatedData['reCreate']['due_date'], $timezone))->setTimezone('UTC');
                $new->status_id = 1;
                $new->result = null;
                $new->result_at = null;
                $new->type_id = $validatedData['reCreate']['type_id'];
                $new->executor_id = $validatedData['reCreate']['executor_id'];

                $new->save();

                RecordCreateService::handler($new);

                $new->load([
                    'subscribers',
                    'author:id,name,email',
                    'executor:id,name,email'
                ]);
                $new = $this->formatRecord($new);
            }


            if (!empty($validatedData['returnFormat']) && $validatedData['returnFormat'] == 'communicationPanel') {
                $response = ['success' => true];
                $response['record'] = $this->getCommunicationPanelFormat($task);
                if (isset($new)) {
                    $new->load([
                        'type',
                        'author.employee:id,name,l_name',
                        'executor.employee:id,name,l_name'
                    ]);
                    $response['new_record'] = $this->getCommunicationPanelFormat($new);
                }
            } else {
                $response = [
                    'success' => true,
                    'record' => $this->formatRecord($task),
                ];
                if (isset($new)) {
                    $response['new_record'] = $new;
                }
            }
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
            ->json($response);
    }

    /**
     * Format and add a data about client for records.
     * @param Collection $records
     * @return Collection
     */
    private function formatRecords(Collection $records): Collection
    {
        $client_ids = [];

        $records->each(function ($item) use (&$client_ids) {
            if (isset($item->miscs['relation']['type'], $item->miscs['relation']['id'])) {
                $client_id = $item->miscs['relation']['client_id'] ?? null;

                if ($client_id && !in_array($client_id, $client_ids, true)) {
                    $client_ids[] = $client_id;
                }
                if ($item->miscs['relation']['type'] === 'client' &&
                    !in_array($item->miscs['relation']['id'], $client_ids, true)) {
                    $client_ids[] = $item->miscs['relation']['id'];
                }
            }
        });


        // Get clients data
        $clients = Client::query()
            ->clientPhones()
            ->clientEmails()
            ->find($client_ids, ['id', 'name', 'lname']);


        // Apply data
        $records->transform(function ($item) use ($clients) {
            if (isset($item->miscs['relation']['type'], $item->miscs['relation']['id'])) {
                $client_id = $item->miscs['relation']['client_id'] ?? null;

                if (!$client_id && $item->miscs['relation']['type'] === 'client') {
                    $client_id = $item->miscs['relation']['id'];
                }

                $client = $clients->find($client_id);
                $item = $this->applyClientData($client, $item);
            }

            return $item;
        });

        return $records;
    }

    /**
     * Format single record.
     * @param $item
     * @return mixed
     */
    private function formatRecord($item)
    {
        if (isset($item->miscs['relation']['type'], $item->miscs['relation']['id'])) {
            $client_id = $item->miscs['relation']['client_id'] ?? null;

            if (!$client_id && $item->miscs['relation']['type'] === 'client') {
                $client_id = $item->miscs['relation']['id'];
            }

            $client = Client::query()
                ->clientPhones()
                ->clientEmails()
                ->find($client_id, ['id', 'name', 'lname']);
            $item = $this->applyClientData($client, $item);
        }

        return $item;
    }

    /**
     * Add client data.
     * @param $client
     * @param $item
     * @return mixed
     */
    private function applyClientData($client, $item)
    {
        if ($client) {
            $value = $type = null;
            if ($phone = $client->phones->first()) {
                $value = $phone->value;
                $type = 'phone';
            }
            if (!$value && $email = $client->emails->first()) {
                $value = $email->value;
                $type = 'email';
            }

            $item->client = [
                'name' => $client->name,
                'lname' => $client->lname,
                'value' => [
                    'type' => $type,
                    'value' => $value,
                ]
            ];
        }
        return $item;
    }

}
