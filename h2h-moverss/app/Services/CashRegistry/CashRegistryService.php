<?php

namespace App\Services\CashRegistry;

use App\Enums\CashRegistry\OperationType;
use App\Enums\Common\DateFormat;
use App\Models\CashRegistry\CashRegistry;
use App\Models\CashRegistry\CashRegistryItem;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class CashRegistryService
{
    public function create(Employee $employee): CashRegistry
    {
        $model = new CashRegistry();
        $model->employee_id = $employee->id;
        $model->active = true;
        $model->cash_on_hand = 0;

        $model->save();

        return $model;
    }

    private function updateBalanceNextOperations(
        CashRegistry $cashRegistry,
        CashRegistryItem $cashRegistryItem,
    ): void
    {
        CashRegistryItem::query()
            ->where('cash_registry_id', $cashRegistry->id)
            ->where('insert_date', '>', $cashRegistryItem->insert_date)
            ->each(function (CashRegistryItem $item) use ($cashRegistryItem){
                $item->update([
                    'balance' => $item->balance + $cashRegistryItem->sum,
                ]);
            });

    }

    public function addOperation(array $data): CashRegistryItem
    {
        $cashRegistry = CashRegistry::query()
            ->where('employee_id', $data['employee_id'])
            ->first();

        return make_transaction(function () use ($cashRegistry, $data) {
            /** @var CashRegistry $cashRegistry */

            $model = new CashRegistryItem();
            $model->cash_registry_id = $cashRegistry->id;
            $model->type = $data['type'];
            $model->insert_date = $data['insert_at'];
            $model->executor_id = auth()?->user()?->employee->id;

            if(
                $model->type->isCashTransfer()
                || $model->type->isPayrollCashPaid()
                || $model->type->isCashCollection()
            ){
                $model->sum = -$data['sum'];
            } else {
                $model->sum = $data['sum'];
            }

            // получаем предыдущую операцию для получения его баланса
            $previousTransaction = CashRegistryItem::query()
                ->where('cash_registry_id', $cashRegistry->id)
                ->where('insert_date', '<=', $data['insert_at'])
                ->orderBy('insert_date', 'desc')
                ->first();

            if($model->type->isPayrollCashPaid()){
                CashRegistryItem::query()
                    ->where('cash_registry_id', $cashRegistry->id)
//                    ->where('insert_date', '<=', $data['insert_at'])
                    ->orderBy('insert_date', 'desc');
//                    ->first();
            }


            // добавляем баланс (какой баланс у сотрудника на момент операции с учетом суммы текущей операции)
            if(is_null($previousTransaction)){
                $model->balance = $model->sum;
            } else {
                $model->balance = $previousTransaction->balance + $model->sum;
            }

            // если операция произошла в середине других операций (добавлена задним числом)
            // обновляем баланс для всех последующих операций
            $this->updateBalanceNextOperations($cashRegistry, $model);

            $model->save();

            // если у нас трансфер, то обновить данные и для того кому передаем кеш
            if($model->type->isCashTransfer()){
                $toCashRegistry = CashRegistry::query()
                    ->where('employee_id', $data['to_employee_id'])
                    ->first();

                // обновляем сколько кеша на руках
                $toCashRegistry->cash_on_hand += $data['sum'];
                $toCashRegistry->save();

                $toModel = new CashRegistryItem();
                $toModel->cash_registry_id = $toCashRegistry->id;
                $toModel->type = $data['type'];
                $toModel->insert_date = $data['insert_at'];
                $toModel->sum = $data['sum'];
                $toModel->executor_id = $model->executor_id;

                $previousToTransaction = CashRegistryItem::query()
                    ->where('cash_registry_id', $toCashRegistry->id)
                    ->where('insert_date', '<', $data['insert_at'])
                    ->orderBy('insert_date', 'desc')
                    ->first();

                if(is_null($previousToTransaction)){
                    $toModel->balance = $model->sum;
                } else {
                    $toModel->balance = $previousToTransaction->balance + $toModel->sum;
                }

                $toModel->save();

                $this->updateBalanceNextOperations($toCashRegistry, $toModel);
            }

            // обновляем сколько кеша на руках
            $cashRegistry->cash_on_hand += $model->sum;
            $cashRegistry->save();

            return $model;
        });
    }

    private function builderItem(
        array $filter = []
    ): Builder
    {
        return CashRegistryItem::query()
            ->filter($filter)
            ->with([
                'executor',
                'foreman',
            ])
            ->whereHas('cashRegistry', function (Builder $query){
                $query->where('active', true);
            })
            ->when(isset($filter['division_id']), function (Builder $query) use ($filter) {
                $query->whereHas('foreman', function (Builder $query) use ($filter) {
                    $query->whereJsonContains('division_ids', $filter['division_id']);
                });
            })
            ->orderBy('insert_date', 'desc')
            ;
    }

    public function getPagination(
        array $filter = []
    ): LengthAwarePaginator
    {
        $result = $this->builderItem($filter)
            ->paginate(
                perPage:30,
                page: $filter['page'] ?? 1
            );

        $items = $result->getCollection();

        $transformedItems = $items->map(function (CashRegistryItem $model) {
            return $this->formatItemDataForCrm($model);
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
        $items = $this->builderItem($filter)
            ->get();

        return $items->map(function (CashRegistryItem $model) {
            return $this->formatItemDataForCrm($model);
        });
    }

    public function formatItemDataForCrm(CashRegistryItem $model): array
    {
        $executor['executor'] = null;
        if($model->executor){
            $executor['executor'] = [
                'id' => $model->executor->id,
                'name' => $model->executor->full_name,
            ];
        }

        $foreman['foreman'] = null;
        if($model->foreman){
            $foreman['foreman'] = [
                'id' => $model->foreman->id,
                'name' => $model->foreman->full_name,
            ];
        }

        $result = array_merge([
            'id' => $model->id,
            'type' => $model->type,
            'sum' => $model->sum,
            'insert_at' => $model->insert_date,
        ],
            $executor,
            $foreman
        );

        return $result;
    }

    public function getCashOnHand(Collection $items): float
    {
        $sum = 0;
        $items->each(function (CashRegistryItem $item) use (&$sum){
            if($item->type->isCashCollection()){
                $sum -= $item->sum;
            }
            if($item->type->isCashDisbursement()){
                $sum += $item->sum;
            }
            if($item->type->isPayrollCashCollected()){
                $sum += $item->sum;
            }
            if($item->type->isPayrollCashPaid()){
                $sum -= $item->sum;
            }
            if($item->type->isCashTransfer()){
                if($item->sum > 0){
                    $sum += $item->sum;
                } else {
                    $sum -= abs($item->sum);
//                    $sum -= $item->sum;
                }
            }
        });

        return $sum;
    }

    public function getBalance(
        string $fromDate,
        string $toDate,
        int $employeeId
    ): array
    {
        $from = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $fromDate, DateFormat::TZ_CHICAGO())
            ->startOfDay();
        $to = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $toDate, DateFormat::TZ_CHICAGO())
            ->endOfDay();

        $cashRegistry = CashRegistry::query()
            ->where('employee_id', $employeeId)
            ->first();

        $itemsPrevious = CashRegistryItem::query()
            ->select(['balance'])
            ->where('cash_registry_id', $cashRegistry->id)
            ->where('insert_date', '<', $from)
            ->orderBy('insert_date', 'desc')
            ->first();

        $itemsBalance = CashRegistryItem::query()
            ->select(['balance'])
            ->where('cash_registry_id', $cashRegistry->id)
            ->where('insert_date', '<', $to)
            ->orderBy('insert_date', 'desc')
            ->first();

        $meta['previous_balance'] = $itemsPrevious ? $itemsPrevious->balance : 0;
        $meta['balance_end_period'] = $itemsBalance ? $itemsBalance->balance : 0;

        return $meta;
    }
}
