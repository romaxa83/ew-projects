<?php

namespace WezomCms\Dealerships\Traits;

use Illuminate\Database\Eloquent\Builder;

trait RateCountTrait
{
    public function scopeCountsRate(Builder $query)
    {
        $query->withCount([
            'ordersDoneWithOrderRate as rating_order_sum' => function($q){
                $q->select(\DB::raw('SUM(rating_order)'));
            },
            'ordersDoneWithOrderRate as rating_order_count' => function($q){
                $q->select(\DB::raw('COUNT(*)'));
            },
            'ordersDoneWithServiceRate as rating_services_sum' => function($q){
                $q->select(\DB::raw('SUM(rating_services)'));
            },
            'ordersDoneWithServiceRate as rating_services_count' => function($q){
                $q->select(\DB::raw('COUNT(*)'));
            },
            'ordersDone as order_done_count' => function($q){
                $q->select(\DB::raw('COUNT(*)'));
            },
        ]);
    }

    public function ordersDoneWithServiceRate()
    {
        return $this->ordersDone()
            ->where('rating_services','!=', null)
            ->where('rating_services','!=', 0)
            ;
    }

    public function ordersDoneWithOrderRate()
    {
        return $this->ordersDone()
            ->where('rating_order','!=', null)
            ->where('rating_order','!=', 0)
            ;
    }

    public function countDoneOrder()
    {
        if(isset($this->order_done_count)){
            return $this->order_done_count;
        }

        return 0;
    }

    /**
     * возвращает среднюю ариф. оценки (service) по выполненым и оцененым заявкам
     */
    public function averageServiceRate()
    {
        if(isset($this->rating_services_sum)
            && $this->rating_services_sum != null
            && $this->rating_services_sum !== '0'
            && isset($this->rating_services_count)
            && $this->rating_services_count !== 0
        ){
            return round((int)$this->rating_services_sum/$this->rating_services_count,1);
        }

        return 0;
    }

    /**
     * возвращает кол-во заявок, оцененых (service)
     */
    public function countDoneServiceRate()
    {
        if(isset($this->rating_services_count)){
            return $this->rating_services_count;
        }

        return 0;
    }

    /**
     * возвращает процентное соотношение закрытых заявок к оцененым
     */
    public function percentDoneServiceRate()
    {
        if($this->countDoneOrder() !== 0 && $this->countDoneServiceRate() !== 0){
            return ($this->countDoneServiceRate() * 100)/$this->countDoneOrder();
        }

        return 0;
    }

    public function renderProgressBarServiceRate()
    {
        return '<a href="'. route('admin.services-orders-rates.index',['dealership' => $this->id]) .'">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar"
                            role="progressbar"
                            style="width: '. $this->percentDoneServiceRate() .'%;"
                            aria-valuenow="25"
                            aria-valuemin="0"
                            aria-valuemax="100">( ' . $this->countDoneServiceRate(). '/' .$this->averageServiceRate() .')
                        </div>
                    </div>
                </a>';
    }

    public function averageOrderRate()
    {
        if(isset($this->rating_order_sum)
            && $this->rating_order_sum != null
            && $this->rating_order_sum !== '0'
            && isset($this->rating_order_count)
            && $this->rating_order_count !== 0
        ){
            return round((int)$this->rating_order_sum/$this->rating_order_count,1);
        }

        return 0;
    }

    public function countDoneOrderRate()
    {
        if(isset($this->rating_services_count)){
            return $this->rating_services_count;
        }

        return 0;
    }

    public function percentDoneOrderRate()
    {
        if($this->countDoneOrder() !== 0 && $this->countDoneOrderRate() !== 0){
            return ($this->countDoneOrderRate() * 100)/$this->countDoneOrder();
        }

        return 0;
    }

    public function renderProgressBarOrderRate()
    {
        return '<a href="'. route('admin.services-orders-rates.index',['dealership' => $this->id]) .'">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar"
                            role="progressbar"
                            style="width: '. $this->percentDoneOrderRate() .'%;"
                            aria-valuenow="25"
                            aria-valuemin="0"
                            aria-valuemax="100">( ' . $this->countDoneOrderRate(). '/' .$this->averageOrderRate() .')
                        </div>
                    </div>
                </a>';
    }


}
