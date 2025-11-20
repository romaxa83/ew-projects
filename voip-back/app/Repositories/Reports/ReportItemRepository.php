<?php

namespace App\Repositories\Reports;

use App\Entities\Reports\ReportItemAdditionalEntity;
use App\Models\Reports;
use App\Models\Reports\Item;
use App\Repositories\AbstractRepository;

final class ReportItemRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Reports\Item::class;
    }

    public function getAdditionalData(
        array $filters = [],
    ): ReportItemAdditionalEntity
    {
        $tmp = [
            'total_calls' => 0,
            'total_dropped' => 0,
            'total_wait' => 0,
            'total_time' => 0,
        ];

        $this->getModelsBuilder(
            [],
            $filters,
        )
            ->get()
            ->each(function(Item $model) use(&$tmp){
                $tmp['total_calls'] += 1;
                $tmp['total_wait'] += $model->wait;
                $tmp['total_time'] += $model->total_time;
                if($model->status->isNoAnswer()){
                    $tmp['total_dropped'] += 1;
                }
            })
        ;

        return new ReportItemAdditionalEntity($tmp);
    }
}
