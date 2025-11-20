<?php

namespace App\Http\Controllers\Api\Site;

use App\DTO\Report\ReportDto;
use App\Helpers\Logger\ReportLogger;
use App\Helpers\ReportHelper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Report\AttachVideoRequest;
use App\Http\Request\Report\CreateReportRequest;
use App\Http\Request\Report\UpdatePsReportRequest;
use App\Http\Request\RequestSearch;
use App\Models\JD\EquipmentGroup;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Repositories\Feature\FeatureRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\Report\ReportRepository;
use App\Resources\Custom\CustomReportPdfResource;
use App\Resources\Report\ReportFeatureListResource;
use App\Resources\Report\ReportFeatureResource;
use App\Resources\Report\ReportListResource;
use App\Resources\Report\ReportResource;
use App\Services\Report\ReportService;
use App\Services\Telegram\TelegramDev;
use App\Traits\StoragePath;
use Illuminate\Http\Request;
use PDF;

class ReportController extends ApiController
{
    use StoragePath;

    public function __construct(
        protected ReportService $service,
        protected ReportRepository $repo,
        protected EquipmentGroupRepository $equipmentGroupRepository,
        protected FeatureRepository $featuresRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/report/{report}",
     *     tags = {"Report"},
     *     summary="Получение отчета",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *
     *     @OA\Response(response="200", description="Report Resource",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function show(Report $report)
    {
        return $this->successJsonMessage(ReportResource::make($report->load([
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
            'reportMachines.modelDescription.product',
            'reportMachines.modelDescription.product.sizeParameter',
            'reportMachines.manufacturer',
            'features.feature',
            'features.feature.translations',
            'features.feature.current',
            'features.value',
        ])));
    }

    /**
     * @OA\Post (
     *     path="/api/report/create",
     *     tags = {"Report"},
     *     summary="Создание отчета",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CreateReportRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Report Resource",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function create(CreateReportRequest $request)
    {
        /** @var $user User */
        $user = \Auth::user();
        try {
            $dto = ReportDto::byRequest($request->all())->setUser($user);

            ReportLogger::INFO('CREATE [request]', $request->all());
            ReportLogger::INFO('CREATE [dto]', (array)$dto);

            /** @var $report Report */
            $report = $this->service->create($dto);
//            $report = $this->reportService->create($request->all(), $user);

            TelegramDev::info("📝 CREATE REPORT [{$report->id}]", $user->login . " [{$user->id}]");

            return $this->successJsonMessage(
                ReportResource::make($report)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/report/update-ps/{report}",
     *     tags = {"Report"},
     *     summary="Редактирование отчета ps'ом",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdatePsReportRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Report Resource",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function update(UpdatePsReportRequest $request, Report $report)
    {
        /** @var $user User */
        $user = \Auth::user();
        try {
            if(!$report->isOwner($user)){
                throw new \Exception(__('message.report_not_edit'));
            }
            if(!($report->isOpenEdit() || $report->isProcessCreated())){
                throw new \Exception(__('message.report_not_open_for_edit'));
            }

            $dto = ReportDto::byRequest($request->all())->setUser($user)->setReportID($report->id);

            ReportLogger::INFO("UPDATE [{$report->id}] [request]", $request->all());
            ReportLogger::INFO("UPDATE [{$report->id}] [dto]", (array)$dto);

            $report = $this->service->updatePs($dto, $report);
//            $report = $this->reportService->updatePs($request, $report, $user);

            TelegramDev::info("📝 EDIT REPORT [{$report->id}]", $user->login . " [{$user->id}]");

            return $this->successJsonMessage(
                ReportResource::make($report)
            );
        } catch (\Exception $e){
            return $this->errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/report/attach-video/{report}",
     *     tags = {"Report"},
     *     summary="Привязать видео к отчету",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(type="object", title="Attach Video Request",
     *                 @OA\Property(property="video", type="file", format="binary",
     *                     description="Видео файл",
     *                  ),
     *              required={"video"}
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(response="200", description="Report Resource",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function attachVideo(AttachVideoRequest $request, Report $report)
    {
        try {
            $report = $this->service->attachVideo($request['video'], $report);

            return $this->successJsonMessage(
                ReportResource::make($report)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/report/search",
     *     tags = {"Report"},
     *     summary="Поиск отчетов",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", required=false,
     *          description="Количество записей на странице",
     *          @OA\Schema(type="integer", example="15", default=10)
     *     ),
     *     @OA\Parameter(name="search", in="query", required=false,
     *          description="Cтрока для поиска, которая будет искать по таким полям: \
               - имя дилера, \
               - название equipment group, \
               - серийный номер машины, \
               - название компании клиента, \
               - фамилия ps,
               ",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/ReportListCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function search(RequestSearch $request)
    {
        try {
            return ReportListResource::collection(
                $this->repo->getAllForSearch($request->all())
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/report/export/pdf/{report}",
     *     tags = {"Report"},
     *     summary="Выгрузка отчета в pdf",
     *     description="Генерирует pdf - файл, с данными по отчету, и возвращает ссылку на файл",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="ссылка на файл",
     *                  example="http://192.168.144.1/storage/pdf-report/Agrotek-invest_ТОВ_\'НВП_СОРТОСТАНЦІЯ\'_6195m_17-05-2021.pdf"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function exportPdf($id)
    {
        try {
            if(!file_exists($this->getPdfStoragePath())){
                mkdir($this->getPdfStoragePath(), 0777);
            }
            /** @var $report Report */
            $report = $this->repo->getBy('id', $id);
            if(null == $report){
                throw new \DomainException("Not found report [{$id}]");
            }

            $title = ReportHelper::titleForPdf($report->title);

            PDF::loadView('admin.report.pdf.index',
                resolve(CustomReportPdfResource::class)->fill($report),
                [],
                'UTF-8')
                ->setOptions(['logOutputFile' => null, 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->save("{$this->getPdfStoragePath()}{$title}.pdf")
            ;

            return $this->successJsonMessage(['link' => $this->getUrlForPdf($title)]);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/report-feature/{equipmentGroup}",
     *     tags = {"Features", "Report"},
     *     summary="Получение характеристик для equipmentGroup",
     *     description="В МП, хактеристики (поля таблицы), подтягиваются по equipment group, у каждого свой набор, у некоторых их нет ",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{equipmentGroup}", in="path", required=true,
     *          description="ID equipment group",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *     @OA\Parameter(name="type", in="query", required=false,
     *          description="Фильтр по типу предназначения характеристики (1-ground, 2-machine)",
     *          @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="ReportFeature Resource", type="object",
     *                  ref="#/components/schemas/ReportFeatureResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function feature(Request $request, $egId)
    {
        try {
            /** @var $model  EquipmentGroup*/
            $model = $this->equipmentGroupRepository->findBy('id', $egId, [
                'features',
                'features.values',
                'features.values.current',
                'features.current',
                'features.translations',
            ]);

            return $this->successJsonMessage(
                ReportFeatureResource::collection(
                    $model->featuresActive($request->get('type'))
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/report-feature",
     *     tags = {"Features", "Report"},
     *     summary="Получение всех характеристик, для отчета",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="type", in="query", required=false,
     *          description="Фильтр по типу предназначения характеристики (1-ground, 2-machine)",
     *          @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="ReportFeature Resource", type="object",
     *                  ref="#/components/schemas/ReportFeatureListResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function allFeature(Request $request)
    {
        try {
            $features = $this->featuresRepository->getAllByType(true, $request->get('type'));

            return $this->successJsonMessage(
                ReportFeatureListResource::collection($features)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/report/download-video/{report}",
     *     tags = {"Report"},
     *     summary="Скачивание видео",
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function downloadVideo(Report $report)
    {
        try {
            return response()->download($this->pathToVideo($report));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
