<?php

namespace App\Resources\Report;

use App\Helpers\DateFormat;
use App\Models\Report\Report;
use App\Models\Report\ReportMachine;
use App\Models\User\User;
use App\Resources\JD\EquipmentGroupResource;
use App\Resources\JD\ModelDescriptionResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Report Client Resource",
 *     @OA\Property(property="john_dear_client", type="array", description="Клиенты johnDeer",
 *         @OA\Items(ref="#/components/schemas/JdClient")
 *     ),
 *     @OA\Property(property="report_client", type="array", description="Клиенты только для отчета",
 *         @OA\Items(ref="#/components/schemas/ReportClient")
 *     ),
 * )
 */
class ReportClientResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Report $report */
        $report = $this;

        return [
            'john_dear_client' => JdClient::collection($report->clients),
            'report_client' => ReportClient::collection($report->reportClients),
        ];
    }
}
