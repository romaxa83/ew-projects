<?php

namespace App\Resources\Report;

use App\Models\Report\Report;
use App\Resources\Custom\CustomReportFeatureValueResource;
use App\Resources\JD\DealerResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Report List Statistic Resource",
 *     @OA\Property(property="id", type="integer", description="ID report", example=6),
 *     @OA\Property(property="dealer", type="object", description="Dealer Resource",
 *         ref="#/components/schemas/DealerResource"
 *     ),
 *     @OA\Property(property="machine", type="array", description="Machine data",
 *         @OA\Items(ref="#/components/schemas/ReportMachineResource")
 *     ),
 *     @OA\Property(property="features", type="array", description="Features data",
 *         @OA\Items(ref="#/components/schemas/CustomReportFeatureValueResource")
 *     ),
 * )
 */

class ReportListStatisticResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Report $report */
        $report = $this;

        return [
            'id' => $report->id,
            'dealer' => DealerResource::make($report->user->dealer),
            'machine' => ReportMachineResource::collection($report->reportMachines),
            'features' => \App::make(CustomReportFeatureValueResource::class)->fill($report->features)
        ];
    }

    private function ownerReportForPS($report)
    {
        $user = \Auth::user();
        if($user->isPS()){
            if($user->id === $report->user_id){
                return true;
            }
            return false;
        }

        return false;
    }
}

