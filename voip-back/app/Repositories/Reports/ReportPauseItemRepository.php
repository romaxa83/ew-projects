<?php

namespace App\Repositories\Reports;

use App\Entities\Reports\ReportPauseItemAdditionalEntity;
use App\Models\Reports;
use App\Repositories\AbstractRepository;

final class ReportPauseItemRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Reports\PauseItem::class;
    }

    public function getAdditionalData(
        array $filters = [],
    ): ReportPauseItemAdditionalEntity
    {
        $tmp = [
            'pause' => 0,
            'total_pause_time' => 0,
        ];

        $this->getModelsBuilder(
            [],
            $filters,
        )
            ->get()
            ->each(function(Reports\PauseItem $model) use(&$tmp){
                $tmp['pause'] += 1;
                $tmp['total_pause_time'] += $model->getDiffAtBySec();
            })
        ;

        return new ReportPauseItemAdditionalEntity($tmp);
    }
}
