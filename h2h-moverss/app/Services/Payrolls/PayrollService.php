<?php

namespace App\Services\Payrolls;

use App\Enums\CashRegistry\OperationType;
use App\Enums\Common\DateFormat;
use App\Http\Requests\Api\Payroll\PayrollStoreRequest;
use App\Models\Audit;
use App\Models\Order;
use App\Models\Order\Payroll;
use App\Models\User\Role;
use App\Services\CashRegistry\CashRegistryService;
use Auth;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PayrollService
{
    public function __construct(
        protected CashRegistryService $cashRegistryService
    )
    {}

    private function builder(
        array $filter = []
    ): Builder
    {
        return Payroll\Payroll::query()
            ->filter($filter)
            ->with([
                'order.client',
                'items.employee',
                'items.role',
                'processedEmployee',
                'creator'
            ]);
    }


    public function getPagination(
        array $filter = []
    ): LengthAwarePaginator
    {
        $result = $this->builder($filter)
            ->paginate(
                perPage: 20,
                page: $filter['page'] ?? 1
            );

//        dd($filter);

        $items = $result->getCollection();

        $transformedItems = $items->map(function (Payroll\Payroll $model) {
            return $this->formatDataForCrm($model);
        });

        return new LengthAwarePaginator(
            $transformedItems,
            $result->total(),
            $result->perPage(),
            $result->currentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]
        );
    }

    public function getCollection(
        array $filter = []
    ): Collection
    {
        $result = collect();

        $this->builder($filter)
            ->get()
            ->each(function (Payroll\Payroll $model) use (&$result) {
                $result->push($this->formatDataForCrm($model));
            });

        return $result;
    }


    public function getDataToCrm(int $orderId): array
    {
        $result = [];

        $payroll = Payroll\Payroll::query()
            ->with([
                'processedEmployee',
                'items.employee',
                'items.role',
                'creator'
            ])
            ->where('order_id', $orderId)
            ->first();

        if(!$payroll) return $result;


        return $this->formatDataForCrm($payroll);
    }

    public function formatDataForCrm(Payroll\Payroll $model): array
    {
        $paidData = $model->getPaidFromBol();

        $processedEmployee['processedEmployee'] = null;
        if($model->processedEmployee){
            $processedEmployee['processedEmployee'] = [
                'id' => $model->processedEmployee->id,
                'name' => $model->processedEmployee->full_name,
            ];
        }

        $client['client'] = null;
        if(!is_null($model->order->client_id)){
            $client['client'] = [
                'id' => $model->order->client_id,
                'name' => $model->order->client->full_name,
            ];
        }

        $result = array_merge( [
            'id' => $model->id,
            'order_id' => $model->order_id,
            'hours' => $model->hours,
            'is_processed' => $model->is_processed,
            'processed_at' => $model->processed_at,
            'sum_cash_paid' => $model->getSumCashPaid(),
            'sum_cc_paid' => $model->getSumCCPaid(),
            'cash_on_hands_result' => $model->cash_on_hand ?? 0,
            'margin' => $model->getMargin(),
            'margin_cash' => $model->getMarginCash(),
            'meta' => $model->getActionMeta(),
            'created_at' => $model->created_at,
            'start_at' => $model->start_at,
            'end_at' => $model->end_at,
            'creator' => $model->creator ? [
                'id' => $model->creator->id,
                'name' => $model->creator->full_name,
            ] : null,
        ],
            $paidData,
            $processedEmployee,
            $client,
        );

        foreach ($model->items->sortBy('role_id') as $item) {
            /** @var $item Payroll\Item */
            $result['items'][] = [
                'employee_id' => $item->employee_id,
                'employee_name' => $item->employee->full_name,
                'role_id' => $item->role_id,
                'role_name' => $item->role->title,
                'hourly_rate' => $item->hourly_rate,
                'hours' => $item->hours,
                'extras' => $item->extras,
                'is_cc_due' => $item->is_cc_due,
                'sub_total' => $item->getSubTotal(),
                'cash_paid' => $item->getCashPaid(),
                'cc_due_paid' => $item->getCCPaid(),
            ];
        }


        return $result;
    }

    public function createForRequest(PayrollStoreRequest $request, $orderId): Payroll\Payroll
    {
        $data = $request->validated();

        return make_transaction(function () use ($data, $orderId) {
            $user = Auth::user();
            $user->load('employee');

            $order = Order::query()
                ->with('mobileEstimate')
                ->where('id', $orderId)
                ->first();

            $mobileEstimate = $order->mobileEstimate;

            $paid = $mobileEstimate->bol['paid'] ?? [];

            $teams = $mobileEstimate->bol['teams'] ?? [];

            list($start, $end) = $mobileEstimate->getBolWorkDurations();

            $payroll = new Payroll\Payroll();
            $payroll->order_id = $orderId;
            $payroll->hours = $mobileEstimate->getBolWorkHours();
            $payroll->start_at = $start;
            $payroll->end_at = $end;
            $payroll->paid_form_bol = array_to_json($paid);
            $payroll->is_processed = false;
            $payroll->creator_id = $user->employee->id;
            $payroll->save();

            $audit = Audit::query()
                ->where('auditable_id', $payroll->id)
                ->where('auditable_type', Payroll\Payroll::MORPH_NAME)
                ->where('order_id', $orderId)
                ->first();
            $audit->division_id = $order->division_id;
            $audit->user_type = get_class(auth_user());
            $audit->user_id = auth_user()->id;
            $audit->save();

            foreach ($data['items'] as $item) {
                $hours = 0;
                foreach ($teams as $team) {
                    foreach ($team['workers'] ?? [] as $worker) {
                        if($item['employee_id'] == $worker['id']){
                            if(isset($team['payHours'])) $hours = $team['payHours'];
                        }
                    }
                }

                $i = new Payroll\Item();
                $i->payroll_id = $payroll->id;
                $i->employee_id = $item['employee_id'];
                $i->role_id = $item['role_id'];
                $i->hours = $hours;
                $i->hourly_rate = $item['rate'];
                $i->extras = $item['extra'];
                $i->is_cc_due = $item['is_cc_due'];
                $i->save();

                $a = Audit::query()
                    ->where('auditable_id', $i->id)
                    ->where('auditable_type', Payroll\Item::MORPH_NAME)
                    ->where('event', 'created')
                    ->first()
                ;
                $a->division_id = $order->division_id;
                $a->order_id = $order->id;
                $a->user_type = get_class(auth_user());
                $a->user_id = auth_user()->id;
                $a->save();
            }

            $payroll->cash_on_hand = $payroll->calcCashOnHandsResult();
            $payroll->save();

            return $payroll;
        });
    }

    public function update(Payroll\Payroll $model, array $data): Payroll\Payroll
    {
        return make_transaction(function () use ($model, $data) {

            $paid = json_to_array($model->paid_form_bol);
            $paid['cash'] = $data['cash_collecte'];
            $paid = array_to_json($paid);
            $model->paid_form_bol = $paid;
            $model->save();

            foreach ($data['items'] as $item) {
                /** @var $i Payroll\Item */
                $i = $model->items->where('employee_id', $item['employee_id'])->first();
                $i->role_id = $item['role_id'];
                $i->hourly_rate = $item['hourly_rate'];
                $i->hours = $item['hours'];
                $i->extras = $item['extras'];
                $i->is_cc_due = $item['is_cc_due'];
                $i->save();
            }

            $model->refresh();
            $model->cash_on_hand = $model->calcCashOnHandsResult();
            $model->save();

            return $model->refresh();
        });
    }

    public function toggleProcess(Payroll\Payroll $model): Payroll\Payroll
    {
        return make_transaction(function () use ($model) {
            return $this->toggleProcessExec($model);
        });
    }

    private function toggleProcessExec(Payroll\Payroll $model): Payroll\Payroll
    {
        $model->load('items.employee.user');
        $foreman = null;
        foreach ($model->items as $item) {
            if($item->role_id == Role::FOREMAN_ID){
                $foreman = $item->employee;
            }
        }

        if($model->is_processed){
            $now = CarbonImmutable::now();
            $targetDate = $model->end_at;

            $isSameMonth = $now->isSameMonth($targetDate);

            // Проверка: текущая дата в первые 3 дня следующего месяца от другой даты
            $isWithinNextMonthDays =
                !$isSameMonth &&
                $now->isSameMonth($targetDate->addMonth()) && // Текущий месяц = следующий от другой даты
                $now->day <= 3; // Проверяем, что день <= 3

            if($isSameMonth || $isWithinNextMonthDays){
                $model->is_processed = false;
                $model->processed_at = null;
            } else {
                $tDate = $model->end_at;
                throw new \Exception(
                    "Cannot switch because the current date [{$now->format('Y-m-d')}] is very far from the target date [{$tDate->format('Y-m-d')}]"
                );
            }

            // обновляем(добавляем транзакцию) данные по CashRegistry для формана этого payroll
            if($foreman){
                if(isset($model->getPaidFromBol()['cash']) && $model->getPaidFromBol()['cash']){
                    $this->cashRegistryService->addOperation([
                        'employee_id' => $foreman->id,
                        'sum' => -$model->getPaidFromBol()['cash'],
                        'insert_at' => CarbonImmutable::now(DateFormat::TZ_CHICAGO()),
                        'type' => OperationType::PAYROLL_CASH_COLLECTED,
                    ]);
                    sleep(1);
                }
                if($model->getSumCashPaid()){
                    $this->cashRegistryService->addOperation([
                        'employee_id' => $foreman->id,
                        'sum' => -$model->getSumCashPaid(),
                        'insert_at' => CarbonImmutable::now(DateFormat::TZ_CHICAGO()),
                        'type' => OperationType::PAYROLL_CASH_PAID,
                    ]);
                }
            }
        } else {
            $model->is_processed = true;
            $model->processed_at = CarbonImmutable::now();
            if(is_null($model->processed_employee_id)){
                $model->processed_employee_id = auth()->user()?->employee->id;
            }

            // обновляем(добавляем транзакцию) данные по CashRegistry для формана этого payroll
            if($foreman){
                if(isset($model->getPaidFromBol()['cash']) && $model->getPaidFromBol()['cash']){
                    $this->cashRegistryService->addOperation([
                        'employee_id' => $foreman->id,
                        'sum' => $model->getPaidFromBol()['cash'],
                        'insert_at' => CarbonImmutable::now(DateFormat::TZ_CHICAGO()),
                        'type' => OperationType::PAYROLL_CASH_COLLECTED,
                    ]);
                    // добавляем задержку, чтоб в следующей операции корректно высчитать баланс, т.к.
                    // две операции создаются в одно время, не всегда получить именно последнюю операцию для расчета баланса
                    sleep(1);
                }
                if($model->getSumCashPaid()){
                    $this->cashRegistryService->addOperation([
                        'employee_id' => $foreman->id,
                        'sum' => $model->getSumCashPaid(),
                        'insert_at' => CarbonImmutable::now(DateFormat::TZ_CHICAGO()),
                        'type' => OperationType::PAYROLL_CASH_PAID,
                    ]);
                }
            }
        }

        $model->save();

        return $model->refresh();
    }
}

