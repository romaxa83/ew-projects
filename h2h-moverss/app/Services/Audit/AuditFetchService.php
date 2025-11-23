<?php

namespace App\Services\Audit;

use App\Http\Resources\Audits\AuditLogResource;
use App\Models\Audit;
use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditFetchService
{
    public function __construct(
        protected TransformDataService $transformDataService
    )
    {}

    public const DEFAULT_PER_PAGE = 30;

    public function getAuditQueryByOrder(
        Order $order,
        array $filter = []
    ): Builder
    {
        return Audit::query()
            ->with([
                'user.employee',
                'client',
//                'auditable',
            ])
            ->where('order_id', $order->id)
            ->where('is_show_to_log', true)
            ->when(isset($filter['user_id']), function ($q) use ($filter) {
                $q->where('user_id', $filter['user_id']);
            })
            ->orderBy('created_at', $filter['sort_type'] ?? 'desc');
    }

    public function getAuditPaginatorByOrder(
        Order $order,
        array $filter = []
    ): LengthAwarePaginator
    {
        return $this
            ->getAuditQueryByOrder($order, $filter)
            ->paginate(
                perPage: $filter['per_page'] ?? self::DEFAULT_PER_PAGE,
                page: $filter['page'] ?? 1
            );
    }

    public function getAuditQueryByDispatch(
        array $filter = []
    ): Builder
    {
        return Audit::query()
            ->with(['user.employee', 'client'])
            ->whereIn('auditable_type', [
                DispatchTruck::MORPH_NAME,
                DispatchEmployer::MORPH_NAME,
            ])
            ->where('is_show_to_log', true)
            ->when(isset($filter['start_date']), function ($q) use ($filter) {
                $q->where('dispatch_truck_at', $filter['start_date']);
            })
            ->when(isset($filter['division_id']), function (Builder $q) use ($filter) {
                $q->where(function($q) use($filter) {
                    $q->where('division_id', $filter['division_id'])
                        ->orWhereNull('division_id')
                    ;
                });

            })
            ->orderBy('created_at', $filter['sort_type'] ?? 'desc');
    }

    public function getAuditPaginatorByDispatch(
        array $filter = []
    ): LengthAwarePaginator
    {
        return $this
            ->getAuditQueryByDispatch($filter)
            ->paginate(
                perPage: $filter['per_page'] ?? self::DEFAULT_PER_PAGE,
                page: $filter['page'] ?? 1
            );
    }

    public function byOrderPagination(
        Order $order,
        array $filter = []
    ): array
    {

        return $this->paginationMetaData(
            $this->getAuditPaginatorByOrder($order, $filter)
        );
    }

    public function byOrderList(
        Order $order,
        array $filter = []
    ): array
    {

        $data = $this->getAuditQueryByOrder($order, $filter)
            ->get();

        $data = $this->transformData($data);

        return [
            'data' => AuditLogResource::collection($data),
        ];
    }

    public function byDispatchPagination(
        array $filter = []
    ): array
    {
        return $this->paginationMetaData(
            $this->getAuditPaginatorByDispatch($filter)
        );
    }

    public function byDispatchList(
        array $filter = []
    ): array
    {

        $data = $this->getAuditQueryByDispatch($filter)
            ->get();

        $data = $this->transformData($data);

        return [
            'data' => AuditLogResource::collection($data),
        ];
    }


    public function transformData(LengthAwarePaginator|Collection $data): array
    {
        $tmp = [];

        $items = $data;
        if($data instanceof LengthAwarePaginator){
            $items = $data->items();
        }

        foreach ($items as $k => $item) {
            if(isset($items[$k - 1])){
                // проверяем и не обрабатываем дубликат
                if(
                    $items[$k - 1]->event == $item->event
                    && $items[$k - 1]->auditable_type == $item->auditable_type
                    && $items[$k - 1]->old_values == $item->old_values
                    && $items[$k - 1]->new_values == $item->new_values
                ){
                    continue;
                }
            }

            $fields = $this->transformDataService
                ->forOrder($item);

            foreach ($fields as $field) {
                $tmp[] = $field;
            }
        }

        foreach ($tmp as $k => $i) {
            if(isset($tmp[$k - 1])){
                // проверяем и не обрабатываем дубликат
                if(
                    $tmp[$k - 1]['action'] == $i['action']
                    && $tmp[$k - 1]['entity'] == $i['entity']
                    && $tmp[$k - 1]['details'] == $i['details']
                ){
                    unset($tmp[$k]);
                }
            }
        }

        return array_values($tmp);
    }

    private function paginationMetaData(LengthAwarePaginator $paginator): array
    {
        $data = $this->transformData($paginator);

        return [
            'data' => AuditLogResource::collection($data),
            'links' => $paginator->linkCollection(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from'         => $paginator->firstItem(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'to'           => $paginator->lastItem(),
                'total'        => $paginator->total(),
            ]
        ];
    }
}
