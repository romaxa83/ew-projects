<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ReportHelper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Admin\Report\ListLocationDataForFilter;
use App\Http\Request\Admin\Report\UpdateReportRequest;
use App\Models\Report\Location;
use App\Models\Report\Report;
use App\Notifications\SendReport;
use App\Repositories\Report\LocationRepository;
use App\Repositories\Report\ReportRepository;
use App\Repositories\Report\ReportRepository as NewReportRepository;
use App\Repositories\TranslationRepository;
use App\Resources\Custom\CustomReportPdfResource;
use App\Resources\Report\ReportResource;
use App\Services\CommentService;
use App\Services\Export\ExcelService;
use App\Services\Report\ReportService;
use App\Traits\StoragePath;
use App\Type\ReportStatus;
use Illuminate\Http\Request;
use PDF;

class ReportController extends ApiController
{
    use StoragePath;

    protected $orderBySupport = ['id', 'created_at'];
    protected $defaultOrderBy = 'created_at';

    public function __construct(
        protected ReportService $reportService,
        protected CommentService $commentService,
        protected LocationRepository $reportLocationRepository,
        protected NewReportRepository $repo,
        protected ReportRepository $reportRepository,
        protected ExcelService $excelService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post  (
     *     path="/api/report/edit/{report}",
     *     tags = {"Report"},
     *     summary="Редактирование отчета админом",
     *     description="Админ может редактировать отчет, однако только небольшое кол-во полей",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/UpdateReportRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Отчет",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function update(UpdateReportRequest $request, Report $report)
    {
        try {
            $report = $this->reportService->update($request, $report);

            return $this->successJsonMessage(
                ReportResource::make($report)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/{report}/verify",
     *     tags = {"Report"},
     *     summary="Верифицировать отчет",
     *     description="Верефицировав отчет, он закрывается на редактирование, генериться pdf-файл, и отправляется на почту клиенту (поле - client_email)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200", description="Отчет",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function verify(Report $report)
    {
        try {
            $report = $this->reportService->verify($report);
            $this->commentService->deleteByReport($report);

            $report->refresh();

//            TelegramDev::info("✔ Отчет верефицирован [{$report->id}]", "admin");

            if(!$report->client_email && !filter_var($report->client_email, FILTER_VALIDATE_EMAIL)){
                throw new \Exception(__('message.exceptions.report don\'t have a client email'));
            }

            if(!file_exists($this->getPdfStoragePath())){
                mkdir($this->getPdfStoragePath(), 0777);
            }

            $title = ReportHelper::titleForPdf($report->title);
            PDF::loadView('admin.report.pdf.index', resolve(CustomReportPdfResource::class)->fill($report), [], 'UTF-8')
                ->setOptions(['logOutputFile' => null, 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->save("{$this->getPdfStoragePath()}{$title}.pdf");

            \Notification::route('mail', $report->client_email)
                ->notify(new SendReport($this->getUrlForPdf($title)));

//            TelegramDev::info("📨 Отчет отправлен [{$report->id}]", $report->client_email);

            return $this->successJsonMessage(ReportResource::make($report));
        } catch (\Exception $error){
            \Log::error($error->getMessage());
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/report-list-filter",
     *     tags = {"Report"},
     *     summary="Получение списка локаций для фильтров",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="type", in="query", required=true,
     *          description="Тип данных",
     *          @OA\Schema(type="string", example="region", enum={"country", "region", "district"})
     *     ),
     *     @OA\Parameter(name="forStatistic", in="query", required=false,
     *          description="Формат данных для статистики",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(name="query", in="query", required=false,
     *          description="Строка для поиска",
     *          @OA\Schema(type="string", example="херсо")
     *     ),
     *     @OA\Parameter(name="country", in="query", required=false,
     *          description="Порлучение данных по конкретной стране, (можно передовать несколько через ',')",
     *          @OA\Schema(type="string", example="Poland")
     *     ),
     *
     *     @OA\Response(response="200", description="Success with simple data", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function listLocationDataForFilter(ListLocationDataForFilter $request)
    {
        try {
            $res = [];
            if(Location::checkTypeForFilter($request['type'])){
                $res = $this->reportLocationRepository->getListByFilter(
                    $request['type'],
                    $request['query'],
                    $request['country'] ?? false
                );
            }

            if($request['forStatistic'] && filter_var($request['forStatistic'], FILTER_VALIDATE_BOOLEAN)){
                $res = array_reverse($res);
            }

            return $this->successJsonMessage($res);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/report/export/excel",
     *     tags = {"Report"},
     *     summary="Выгрузка отчетов в excel",
     *     description="Генерируется excel - файл, на основе фильтров, и отдаеться ссылка на файл",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="ps_id", in="query", required=false,
     *          description="Фильтр по пользователю, с ролью ps",
     *          @OA\Schema(type="integer", example=231)
     *     ),
     *     @OA\Parameter(name="dealer_id", in="query", required=false,
     *          description="Фильтр по дилеру",
     *          @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(name="tm_id", in="query", required=false,
     *          description="Фильтр по территориальному менеджеру",
     *          @OA\Schema(type="integer", example=231)
     *     ),
     *     @OA\Parameter(name="equipment_group_id", in="query", required=false,
     *          description="Фильтр по equipment group",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(name="model_description_id", in="query", required=false,
     *          description="Фильтр по model description",
     *          @OA\Schema(type="integer", example=21)
     *     ),
     *     @OA\Parameter(name="machine_serial_number", in="query", required=false,
     *          description="Фильтр по machine serial number",
     *          @OA\Schema(type="string", example="RXT45R")
     *     ),
     *    @OA\Parameter(name="year", in="query", required=false,
     *          description="Фильтр по году",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="created_at", enum={"id", "created_at"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="ссылка на excel - файл",
     *                  example="http://192.168.144.1/storage/excel/reports_1653029546.xlsx"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function exportExcel(Request $request)
    {
        try {

            $reports = $this->repo->getAllReportForExcel([
                'user',
                'user.profile',
                'user.dealer',
                'user.dealer.tm',
                'clients',
                'clients.region',
                'reportClients',
                'location',
                'reportMachines',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription',
                'reportMachines.manufacturer',
                'features.feature',
                'features.value',
            ],
                $request->all(),
                $this->orderDataForQuery(),
                [ReportStatus::IN_PROCESS]
            );

            return $this->successJsonMessage([
                'link' => $this->excelService->generateAndSave($reports)
            ]);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

}
