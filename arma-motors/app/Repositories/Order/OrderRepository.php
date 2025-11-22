<?php

namespace App\Repositories\Order;

use Carbon\Carbon;
use App\Models\Order\Order;
use App\Types\Order\Status;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends AbstractRepository
{
    public function query()
    {
        return Order::query();
    }

    public function countByStatus(Status $status, array $payload = [])
    {
        $query = $this->query()
            ->where('status', $status->getValue());

        foreach ($payload as $field => $value){
            $query->where($field, $value);
        }

        return $query->count();
    }

    public function existOrderByTime($dealershipId, $serviceId, $from, $to, array $statuses): bool
    {
        return $this->query()
            ->with(['additions'])
            ->where('service_id', $serviceId)
            ->whereIn('status', $statuses)
            ->whereHas('additions', function($q) use($dealershipId, $from, $to){
                $q->where('dealership_id', $dealershipId)
                    ->whereBetween('real_date', [$from, $to])
                ;
            })
            ->exists();
    }

    public function getForRemind($from, $to, array $statuses, array $relations = ['additions']): Collection
    {
        return $this->query()
            ->with($relations)
            ->whereIn('status', $statuses)
            ->whereHas('additions', function($q) use($from, $to) {
                $q->where('is_send_remind', false)
                    ->whereBetween('real_date', [$from, $to])
                ;
            })
            ->get();
    }

    public function getByDashboardCountOrder($year, array $brandIds = [])
    {
        return $this->query()
            ->select(['id', 'closed_at'])
            ->with(['additions:order_id,brand_id'])
            ->where('status', Status::CLOSE)
            ->whereYear('closed_at', (string)$year)
            ->whereHas('additions', function(Builder $q) use ($brandIds) {
                $q->whereIn('brand_id', $brandIds);
            })->get()
            ->groupBy([
                function($date) {
                    return Carbon::parse($date->closed_at)->format('m');
                },
                function($date) {
                    return $date->additions->brand_id;
                }
                ], $preserveKeys = true)
            ;
    }

    public function getCountForDashboard(): int
    {
        return $this->query()->count();
    }
}
