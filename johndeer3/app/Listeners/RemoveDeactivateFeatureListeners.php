<?php

namespace App\Listeners;

use App\Events\DeactivateFeature;
use App\Repositories\Report\ReportRepository;
use App\Services\Telegram\TelegramDev;
use App\Type\ReportStatus;

class RemoveDeactivateFeatureListeners
{
    public function __construct(){}

    public function handle(DeactivateFeature $event)
    {
        try {
            $reports = app(ReportRepository::class)->getReportByFeatureAndStatus(
                $event->feature->id,
                ReportStatus::IN_PROCESS
            );

            foreach ($reports as $report){
                $report->features()->where('feature_id', $event->feature->id)->delete();
            }

            TelegramDev::info("Характеристика удалена из {$reports->count()} отчетов");
        }
        catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

