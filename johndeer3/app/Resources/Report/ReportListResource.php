<?php

namespace App\Resources\Report;

use App\Helpers\DateFormat;
use App\Models\Report\Report;
use App\Resources\User\UserResource;
/**
 * @OA\Schema(type="object", title="Report Resource",
 *     @OA\Property(property="id", type="string", description="ID", example=6),
 *     @OA\Property(property="title", type="string", description="Заголовок отчета", example="Agrotek_ТОВ_Дружба-5_8345r_04-07-2020"),
 *     @OA\Property(property="status", type="integer", example=1,
 *         description="Статус (1-создана, 2-открыта для редактирования ps'у, 3-отредактирована ps'ом, 4-отчет в процессе смздания, 5-отчет верефицирован)"
 *     ),
 *     @OA\Property(property="verify", type="boolean", description="Верифицирован отчет", example=false),
 *     @OA\Property(property="owner", type="boolean", description="Возвращает для ps true, если это его отчет, иначе false", example=false),
 *     @OA\Property(property="comment", type="object", description="Comment",
 *         ref="#/components/schemas/ReportCommentResource"
 *     ),
 *     @OA\Property(property="client_email", type="string", description="Email клиента", example="gahovv@rdo.ua"),
 *     @OA\Property(property="created", type="string", description="Создание", example="25.07.2021 20:45"),
 *     @OA\Property(property="machine", type="array", description="Machine data",
 *         @OA\Items(ref="#/components/schemas/ReportMachineResource")
 *     ),
 *     @OA\Property(property="clients", type="object", description="Client data",
 *         ref="#/components/schemas/ReportClientResource"
 *     ),
 *     @OA\Property(property="ps", type="object", description="User", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="tm", type="object", description="User", ref="#/components/schemas/UserSimpleResource"),
 * )
 */
use App\Resources\User\UserSimpleResource;
use Illuminate\Http\Resources\Json\JsonResource;


class ReportListResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Report $report */
        $report = $this;

        $data = [
            'id' => $report->id,
            'title' => $report->title,
            'status' => $report->status,
            'verify' => $report->verify,
            'owner' => $this->ownerReportForPS($report),
            'comment' => ReportCommentResource::make($report->comment),
            'client_email' => $report->client_email,
            'created' => DateFormat::front($report->created_at),
            'machine' => ReportMachineResource::collection($report->reportMachines),
            'clients' => ReportClientResource::make($report),
            'ps' => UserResource::make($report->user),
        ];

        if(isset($report->user->dealer->tm) && $report->user->dealer->tm->isNotEmpty()){
            $data['tm'] = UserSimpleResource::make($report->user->dealer->tm->first());
        } else {
            $data['tm'] = null;
        }

        return $data;
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

    /**
     * @SWG\Definition(definition="ReportListResource",
     *     @SWG\Property(property="id", type="string", example = "ID отчета"),
     *     @SWG\Property(property="title", type="string", example = "title"),
     *     @SWG\Property(property="comment", type="object", example = "Images", ref="#/definitions/ReportComment"),
     *     @SWG\Property(property="client_email", type="string", example = "email клиента"),
     *     @SWG\Property(property="status", type="string", example = "статус отчета (1/2/3 - создана/открыта для редактирования ps'у/отредактирована ps'ом)"),
     *     @SWG\Property(property="owner", type="boolean", example = "Возвращает для ps true, если это его отчет,иначе false"),
     *     @SWG\Property(property="created", type="string", example = "дата создания"),
     *     @SWG\Property(property="machine", type="object", example = "Machine", ref="#/definitions/ReportMachineResource"),
     *     @SWG\Property(property="clients", type="object", example = "Clients", ref="#/definitions/ReportClientResource"),
     *     @SWG\Property(property="ps", type="object", example = "User", ref="#/definitions/UserResource"),
     *     @SWG\Property(property="tm", type="object", example = "User", ref="#/definitions/UserSimpleResource"),
     * )
     */
}
