<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Order\Activity;
use App\Models\Order\Notes;
use App\Models\Tasks\Task;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};
use Str;

class EffectiveActionsReportController extends Controller
{
    /**
     * @var array
     */
    private object $selectedUsers;
    private Carbon $date_start;
    private Carbon $date_end;

    /**
     * Загрузка данных для отчета.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $this->selectedUsers = User::selectedUsersOrActive($request->users)
            ->get(['id', 'name'])->keyBy('id');

        $this->date_start = Carbon::parse($request->date_start)->startOfDay();
        $this->date_end = Carbon::parse($request->date_end)->endOfDay();

        return response()
            ->json([
                'success' => true,
                'report' => $this->generateReport($request->rows),
            ]);
    }

    private function generateReport($rows)
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

            $report[] = $v;
        }

        return $report;
    }

    private function computeDataTasksAdded(int $user_id): int
    {
        return Task::whereUserId($user_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->count();
    }

    private function computeDataEmailsSent(int $user_id): int
    {
        return Activity::whereUserId($user_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->whereType('email')
            ->count();
    }

    private function computeDataNotesAdded(int $user_id): int
    {
        return Notes::whereUserId($user_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->count();
    }

    private function computeDataLeadsCreated(int $user_id): int
    {
        return Order::whereUserId($user_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->count();
    }

    private function computeDataWonLeads(int $user_id): int
    {
        return Order::whereUserId($user_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->statusLeadWon()
            ->count();
    }

    private function computeDataLostLeads(int $user_id): int
    {
        return Order::whereUserId($user_id)
            ->whereBetween('created_at', [$this->date_start, $this->date_end])
            ->statusLeadLost()
            ->count();
    }

    private function computeDataTotalSales(int $user_id): float
    {
        return Order::whereUserId($user_id)
                ->whereBetween('created_at', [$this->date_start, $this->date_end])
                ->whereHas('payments')
                ->withSum('payments', 'amount')
                ->get(['id'])
                ->reduce(function ($total, $item) {
                    return $total + $item->payments_sum_amount;
                }) ?? 0;
    }
}
