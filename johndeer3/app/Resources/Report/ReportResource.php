<?php

namespace App\Resources\Report;

use App\Helpers\DateFormat;
use App\Models\Image;
use App\Models\Report\Report;
use App\Resources\Custom\CustomReportFeatureValueResource;
use App\Resources\JD\DealerResource;
use App\Resources\User\UserResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Report Resource",
 *     @OA\Property(property="id", type="string", description="ID", example=6),
 *     @OA\Property(property="title", type="string", description="Заголовок отчета", example="Agrotek_ТОВ_Дружба-5_8345r_04-07-2020"),
 *     @OA\Property(property="status", type="integer", example=1,
 *         description="Статус (1-создана, 2-открыта для редактирования ps'у, 3-отредактирована ps'ом, 4-отчет в процессе смздания, 5-отчет верефицирован)"
 *     ),
 *     @OA\Property(property="user", type="object", description="User", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="dealer", type="object", description="Dealer", ref="#/components/schemas/DealerResource"),
 *     @OA\Property(property="machine", type="array", description="Machine data",
 *         @OA\Items(ref="#/components/schemas/ReportMachineResource")
 *     ),
 *     @OA\Property(property="clients", type="object", description="Client data",
 *         ref="#/components/schemas/ReportClientResource"
 *     ),
 *     @OA\Property(property="location", type="object", description="Location",
 *         ref="#/components/schemas/ReportLocationResource"
 *     ),
 *     @OA\Property(property="images", type="array", description="Image data",
 *         @OA\Items(ref="#/components/schemas/ReportImageResource")
 *     ),
 *     @OA\Property(property="comment", type="object", description="Comment",
 *         ref="#/components/schemas/ReportCommentResource"
 *     ),
 *     @OA\Property(property="salesman_name", type="string", description="Имя продовца", example="Буряк Игорь"),
 *     @OA\Property(property="assignment", type="string", description="Назначения", example="демонстрация качества среза и обработки силоса"),
 *     @OA\Property(property="result", type="string", description="Результат", example="великолепно"),
 *     @OA\Property(property="client_comment", type="string", description="Комментарии клиента", example="Техніка сподобалась"),
 *     @OA\Property(property="client_email", type="string", description="Email клиента", example="gahovv@rdo.ua"),
 *     @OA\Property(property="created", type="string", description="Создание", example="25.07.2021 20:45"),
 *     @OA\Property(property="updated", type="string", description="Обновление", example="25.07.2021 20:45"),
 *     @OA\Property(property="fill_table_date", type="string", description="Дата заполнения таблицы", example="25.07.2021 20:45"),
 *     @OA\Property(property="verify", type="boolean", description="Верифицирован отчет", example=false),
 *     @OA\Property(property="video", type="object", description="Video",
 *         ref="#/components/schemas/ReportVideoResource"
 *     ),
 *     @OA\Property(property="features", type="array", description="Features data",
 *         @OA\Items(ref="#/components/schemas/CustomReportFeatureValueResource")
 *     ),
 *     @OA\Property(property="planned_at", type="integer", description="Планируемая дата (timestamp с мс.)", example=1651906800000),
 * )
 */
class ReportResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Report $report */
        $report = $this;

        return [
            'id' => $report->id,
            'title' => $report->title,
            'status' => $report->status,
            'user' => UserResource::make($report->user),
            'dealer' => DealerResource::make($report->user->dealer),
            'machine' => ReportMachineResource::collection($report->reportMachines),
            'clients' => ReportClientResource::make($report),
            'location' => ReportLocationResource::make($report->location),
            'images' => $this->getImages($report->images),
            'comment' => ReportCommentResource::make($report->comment),
            'salesman_name' => $report->salesman_name,
            'assignment' => $report->assignment,
            'result' => $report->result,
            'client_comment' => $report->client_comment,
            'client_email' => $report->client_email,
            'created' => DateFormat::front($report->created_at),
            'updated' => DateFormat::front($report->updated_at),
            'fill_table_date' => DateFormat::front($report->fill_table_date),
            'verify' => $report->verify,
            'video' => $report->video ? ReportVideoResource::make($report->video) : null,
            'features' => \App::make(CustomReportFeatureValueResource::class)->fill($report->features, false),
            'planned_at' => isset($report->pushData->planned_at) ? DateFormat::dateToTimestampMs($report->pushData->planned_at) : null
        ];
    }

    private function getImages($reportImages)
    {
        // добавляем в коллекцию подпись с нулами (если ее нету), чтоб были данные для фронта
        $addFakeSignature = true;
        /** @var $reportImages Collection */
        if($reportImages->isNotEmpty()){

            foreach ($reportImages as $image){
                /** @var $image Image */
                if($image->isSignature()){
                    $addFakeSignature = false;
                }
            }

            if($addFakeSignature){
                $new = new Image();
                $new->basename = null;
                $new->model = 'signature';
                $new->url = null;

                $reportImages->push($new);
            }
        }

        return ReportImageResource::collection($reportImages);
    }
}
