<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};
use Str;

class ByManagersAndCompanyReportController extends Controller
{
    private object $selectedUsers;
    private Carbon $date_start;
    private Carbon $date_end;
    private int $division_id;

    /**
     * Загрузка данных для отчета.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $this->division_id = $request->get('division_id', $request->session()->get('division.id'));

        $this->selectedUsers = User::selectedUsersOrActive($request->users)
            ->whereJsonContains('division_ids', $this->division_id)
            ->get(['id', 'name', 'division_ids'])->keyBy('id');

        // FIXME Timezone
        $this->date_start = Carbon::parse($request->date_start)->startOfDay();
        $this->date_end = Carbon::parse($request->date_end)->endOfDay();

        return response()
            ->json([
                'success' => true,
                'report' => $this->generateReportBySales($request->rows),
            ]);
    }

    /**
     * Get users ids where user have an order.
     * @return JsonResponse
     */
    public function usersWithOrders(): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'ids' => Order::query()
                    ->where('user_id', '>', 0)
                    ->groupBy('user_id')
                    ->get(['user_id'])
                    ->pluck('user_id'),
            ]);
    }

    private function generateReportBySales($rows)
    {
        $report = [];

        foreach ($this->selectedUsers as $user) {
            $v = [
                'user' => $user,
            ];
            foreach ($rows as $k) {
                $method = 'computeData'.ucfirst(Str::camel($k));

                $res = 'Method Not Exists - '.$method;
                if (method_exists($this, $method)) {
                    $res = $this->$method($user->id);
                }

                $v['report'][$k] = $res;
            }

            $v['report']['conversion'] = $v['report']['leads_converted'] ?
                number_format(($v['report']['leads_converted'] / $v['report']['leads'] * 100), 2, '.', '') : 0;
            $v['report']['conversion'] .= '%';

            $report[] = $v;
        }

        return $report;
    }

    private function computeDataLeads(int $user_id): int
    {
        // Лидов: количество принятых лидов в системе. Вычитаем от сюда нереализованные лиды Lost с
        // причиной закрытия Duplicate лидов и Spam лиды.
        return Order::whereUserId($user_id)
            ->whereDivisionId($this->division_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            // TODO Причины закрытия у нас не такие, пока без подвязки к деталям
            ->whereNotIn('status_id', [9, 12])
            ->count();
    }

    /**
     * Заказы прошли через статус, без повторов.
     * @param $status_ids mixed ID, или Array
     * @return Order\StatusChangeHistory[]|array|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function ordersPassedStatus($status_ids)
    {
        return Order\StatusChangeHistory::query()
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->when(is_array($status_ids), function ($q, $status_ids) {
                $q->whereIn('new_status', $status_ids);
            }, function ($q) use ($status_ids) {
                $q->where('new_status', $status_ids);
            })
            ->groupBy('order_id')
            ->get(['order_id']);
    }

    private function computeDataTotalSales(int $user_id): float
    {
        // Сумма продаж: сумма в долларах, которая поступила по заказам илр по меннеджеру за указанный период. В том числе и частичных оплат.
        return Order::whereUserId($user_id)
            ->whereDivisionId($this->division_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->whereHas('payments')
            ->withSum('payments', 'amount')
            ->get(['id'])
            ->reduce(function ($total, $item) {
                return $total + $item->payments_sum_amount;
            }) ?? 0;
    }

    private function computeDataSentQuotes(int $user_id): int
    {
        $orders_ids = $this->ordersPassedStatus(4);

        // Отправленных квот: количество уникальных лидов, которые прошли за период на бизнес процесс
        // Calculation Done / Offer Send. Повторные переходы одного и того же лида не учитывать.
        return Order::whereUserId($user_id)
            ->whereDivisionId($this->division_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->whereIn('id', $orders_ids)
            ->count();
    }

    private function computeDataBooked(int $user_id): int
    {
        $orders_ids = $this->ordersPassedStatus(5);

        // Количество booked: количество уникальных заказов, которые за указанный период проходят через БП
        // Booked (Prepayment Done). Повторные переходы одного и того же лида не учитывать.
        return Order::whereUserId($user_id)
            ->whereDivisionId($this->division_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->whereIn('id', $orders_ids)
            ->count();
    }

    private function computeDataLeadsConverted(int $user_id): int
    {
        $orders_ids = $this->ordersPassedStatus(10);

        // Конвертировано лидов: количество лидов, которые перешли на этап Success.
        // Повторные переходы одного и того же лида не учитывать.
        return Order::whereUserId($user_id)
            ->whereDivisionId($this->division_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->whereIn('id', $orders_ids)
            ->count();
    }

    private function computeDataLeadsLost(int $user_id): int
    {
        $orders_ids = $this->ordersPassedStatus(9);

        // Потеряно лидов: количество лидов, которые прошли на бп Lost.
        // Повторные переходы одного и того же лида не учитывать.
        return Order::whereUserId($user_id)
            ->whereDivisionId($this->division_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->whereIn('id', $orders_ids)
            ->count();
    }
}
