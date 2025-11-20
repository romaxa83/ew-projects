<?php

namespace WezomCms\ServicesOrders\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\ServicesOrders\Types\OrderStatus;

class OrderRepository extends AbstractRepository
{
    protected function query()
    {
        return ServicesOrder::query();
    }

    /**
     * @param array $relations
     * @param string $orderField
     * @param array $params
     * @param bool $withPublished
     * @return Collection
     */
    public function getAllByStatuses(
        $userId,
        array $relations = ['translations'],
        string $orderField = 'sort',
        array $params = [],
        array $statuses = [],
        $type = false
    ): Collection
    {
        $this->initParams($params);

        $query = $this->query();

        $query->with($relations)
            ->where('user_id', $userId);

        if(!empty($statuses)){
            $query->whereIn('status', $statuses);
        }

        if(isset($params['from']) && $type){
            if($type == OrderStatus::TYPE_PLANED){
                $query->where('on_date', '>=', DateFormatter::convertTimestampForBack($params['from']));
            }
            if($type == OrderStatus::TYPE_COMPLETED){
                $query->where('closed_at', '>=', DateFormatter::convertTimestampForBack($params['from']));
            }
        }

        if(isset($params['to']) && $type){
            if($type == OrderStatus::TYPE_PLANED){
                $query->where('on_date', '<=', DateFormatter::convertTimestampForBack($params['to']));
            }
            if($type == OrderStatus::TYPE_COMPLETED){
                $query->where('closed_at', '<=', DateFormatter::convertTimestampForBack($params['to']));
            }
        }

        return $query->orderBy($orderField, 'desc')
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->get();
    }

    public function countByStatuses($userId,array $statuses = []): int
    {
        $query = $this->query()->where('user_id', $userId);

        if(!empty($statuses)){
            $query->whereIn('status', $statuses);
        }

        return $query->count();
    }

    public function getAll(
        array $relations = ['translations'],
        string $orderField = 'sort',
        array $params = [],
        bool $withPublished = true,
        string $typeSort = 'asc'
    ): Collection
    {

        $this->initParams($params);

        $query = $this->query()->notReject();

        if($withPublished){
            $query->published();
        }

        $query->with($relations);

        if($this->getSearch()){

            if(in_array('translations', $relations)){
                $query->whereHas('translation', function($q){
                    $q->where('name', 'LIKE', '%' . $this->getSearch() . '%');
                });
            } else {
                $query->where('name', 'LIKE', '%' . $this->getSearch() . '%');
            }
        }

        return $query->orderBy($orderField, $typeSort)
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->get();
    }

    public function count($withPublished = true): int
    {
        $query = $this->query()->notReject();

        if($withPublished){
            $query->published();
        }

        return $query->count();
    }

    public function getOrderForRemind(array $serviceIds, $time)
    {
        $query = $this->query()
            ->with(['user'])
            ->whereIn('service_group_id' ,$serviceIds)
            ->where('status', OrderStatus::ACCEPTED)
            ->where('final_date', '<=', $time)
            ->where('status_notify', '=', 0)
            ->get();

        return $query;
    }
}
