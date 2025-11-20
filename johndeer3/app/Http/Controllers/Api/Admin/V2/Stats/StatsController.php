<?php

namespace App\Http\Controllers\Api\Admin\V2\Stats;

use App\DTO\Stats\StatsDto;
use App\Http\Request\Statistic;
use App\Http\Controllers\Api\ApiController;
use App\Models\Report\Feature\Feature;
use App\Repositories\Feature\FeatureRepository;
use App\Repositories\Report\ReportRepository;
use App\Resources\Report\ReportListStatisticResource;
use App\Services\Statistics\StatisticFilterService;
use App\Services\Statistics\StatisticMachineService;
use App\Services\Statistics\StatisticReportService;

class StatsController extends ApiController
{
    public function __construct(
        protected StatisticMachineService $statisticMachineService,
        protected ReportRepository $reportRepository,
        protected StatisticReportService $statisticReportService,
        protected FeatureRepository $featuresRepository,
        protected StatisticFilterService $serviceFilter
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/machines",
     *     tags={"Statistic"},
     *     summary="Получение данных по статистике для техники (№1)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *              для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=true,
     *          description="Фильтр по дилеру, \
     *              для указания всех стран передаем так - ?dealer=all, \
     *              если нужно передать несколько значений, то так - ?dealer[]=Agristar&dealer[]=Jupiter",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="eg", in="query", required=true,
     *          description="Фильтр по equipment group, передается id ",
     *          @OA\Schema(type="string", example="24")
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id",
     *          @OA\Schema(type="integer", example="44")
     *     ),
     *
     *     @OA\Response(response="200", description="Statistic",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/MachinesResponse"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function machines(Statistic\V2\RequestMachineStatistic $request)
    {
        try {
            $dto = $this->serviceFilter->swapDtoForMachine(StatsDto::byArgs($request->all()));

            return $this->successJsonMessage(
                $this->statisticMachineService->statisticMachines(
                    $this->reportRepository->forMachineStats($dto)
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/machine",
     *     tags={"Statistic"},
     *     summary="Получение данных по статистике для техники (№2)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *              для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=true,
     *          description="Фильтр по дилеру, \
     *              для указания всех стран передаем так - ?dealer=all, \
     *              если нужно передать несколько значений, то так - ?dealer[]=Agristar&dealer[]=Jupiter",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="eg", in="query", required=true,
     *          description="Фильтр по equipment group, передается id ",
     *          @OA\Schema(type="string", example="24")
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id, \
     *              если нужно передать несколько значений, то так - ?md[]=44&md[]=1586",
     *          @OA\Schema(type="integer", example="44")
     *     ),
     *
     *     @OA\Response(response="200", description="Statistic",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportListStatisticResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function machine(Statistic\V2\RequestMachineStatistic $request)
    {
        try {
            $dto = $this->serviceFilter->swapDtoForMachine(StatsDto::byArgs($request->all()));

            return ReportListStatisticResource::collection(
                $this->reportRepository->forMachineStats($dto)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/reports",
     *     tags={"Statistic"},
     *     summary="Получение данных для статистики (№3)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=true,
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован), \
     *              для указания всех статусов передаем так - ?status=all , \
     *              если нужно передать несколько значений, то так - ?status[]=1&status[]=3",
     *          @OA\Schema(type="integer", example=2, enum={1, 2, 3, 4, 5})
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *              для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=true,
     *          description="Фильтр по дилеру, \
     *              для указания всех стран передаем так - ?dealer=all, \
     *              если нужно передать несколько значений, то так - ?dealer[]=Agristar&dealer[]=Jupiter",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="eg", in="query", required=true,
     *          description="Фильтр по equipment group, передается id \
     *              для указания всех значений передаем так - ?eg=all, \
     *              если нужно передать несколько значений, то так - ?eg[]=2&eg[]=6",
     *          @OA\Schema(type="string", example="24")
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id \
     *              для указания всех значений передаем так - ?md=all, \
     *              если нужно передать несколько значений, то так - ?md[]=44&md[]=667",
     *          @OA\Schema(type="integer", example="44")
     *     ),
     *
     *     @OA\Response(response="200", description="Statistic",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportCountResponse"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function reports(Statistic\RequestReportCount $request)
    {
        try {
            $dto = $this->serviceFilter->swapDtoForReport(StatsDto::byArgs($request->all()));

            return $this->successJsonMessage(
                $this->statisticReportService->reportCount($dto)
            );
        } catch (\Exception $error) {
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/type/reports",
     *     tags={"Statistic"},
     *     summary="Получение данных для статистики по type",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=true,
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован), \
     *              для указания всех статусов передаем так - ?status=all , \
     *              если нужно передать несколько значений, то так - ?status[]=1&status[]=3",
     *          @OA\Schema(type="integer", example=2, enum={1, 2, 3, 4, 5})
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *              для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=true,
     *          description="Фильтр по дилеру, \
     *              для указания всех стран передаем так - ?dealer=all, \
     *              если нужно передать несколько значений, то так - ?dealer[]=Agristar&dealer[]=Jupiter",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="eg", in="query", required=true,
     *          description="Фильтр по equipment group, передается id \
     *              для указания всех значений передаем так - ?eg=all, \
     *              если нужно передать несколько значений, то так - ?eg[]=2&eg[]=6",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id \
     *              для указания всех значений передаем так - ?md=all, \
     *              если нужно передать несколько значений, то так - ?md[]=44&md[]=667",
     *          @OA\Schema(type="integer", example=44)
     *     ),
     *     @OA\Parameter(name="type", in="query", required=true,
     *          description="Фильтр по type model description \
     *              для указания всех значений передаем так - ?type=all, \
     *              если нужно передать несколько значений, то так - ?type[]=1&type[]=2",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(response="200", description="Statistic",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportTypeResponse"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function type(Statistic\Type\Report $request)
    {
        try {
            $dto = $this->serviceFilter->swapDtoForReport(StatsDto::byArgs($request->all()));

            return $this->successJsonMessage(
                $this->statisticReportService->reportType($dto)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/size/reports",
     *     tags={"Statistic"},
     *     summary="Получение данных для статистики по size",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=true,
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован), \
     *              для указания всех статусов передаем так - ?status=all , \
     *              если нужно передать несколько значений, то так - ?status[]=1&status[]=3",
     *          @OA\Schema(type="integer", example=2, enum={1, 2, 3, 4, 5})
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *              для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=true,
     *          description="Фильтр по дилеру, \
     *              для указания всех стран передаем так - ?dealer=all, \
     *              если нужно передать несколько значений, то так - ?dealer[]=Agristar&dealer[]=Jupiter",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="eg", in="query", required=true,
     *          description="Фильтр по equipment group, передается id \
     *              для указания всех значений передаем так - ?eg=all, \
     *              если нужно передать несколько значений, то так - ?eg[]=2&eg[]=6",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id \
     *              для указания всех значений передаем так - ?md=all, \
     *              если нужно передать несколько значений, то так - ?md[]=44&md[]=667",
     *          @OA\Schema(type="integer", example=44)
     *     ),
     *     @OA\Parameter(name="size", in="query", required=true,
     *          description="Фильтр по id size \
     *              для указания всех значений передаем так - ?size=all, \
     *              если нужно передать несколько значений, то так - ?size[]=1&size[]=26",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(response="200", description="Statistic",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportSizeResponse"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function size(Statistic\Size\Report $request)
    {
        try {
            $dto = $this->serviceFilter->swapDtoForReport(StatsDto::byArgs($request->all()));

            return $this->successJsonMessage(
                $this->statisticReportService->reportSize($dto)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/crop/reports",
     *     tags={"Statistic"},
     *     summary="Получение данных для статистики по crop",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=true,
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован), \
     *              для указания всех статусов передаем так - ?status=all , \
     *              если нужно передать несколько значений, то так - ?status[]=1&status[]=3",
     *          @OA\Schema(type="integer", example=2, enum={1, 2, 3, 4, 5})
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *              для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=true,
     *          description="Фильтр по дилеру, \
     *              для указания всех стран передаем так - ?dealer=all, \
     *              если нужно передать несколько значений, то так - ?dealer[]=Agristar&dealer[]=Jupiter",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="eg", in="query", required=true,
     *          description="Фильтр по equipment group, передается id \
     *              для указания всех значений передаем так - ?eg=all, \
     *              если нужно передать несколько значений, то так - ?eg[]=2&eg[]=6",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id \
     *              для указания всех значений передаем так - ?md=all, \
     *              если нужно передать несколько значений, то так - ?md[]=44&md[]=667",
     *          @OA\Schema(type="integer", example=44)
     *     ),
     *     @OA\Parameter(name="crop", in="query", required=true,
     *          description="Фильтр по id crop \
     *              для указания всех значений передаем так - ?crop=all, \
     *              если нужно передать несколько значений, то так - ?crop[]=1&crop[]=26",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(response="200", description="Statistic",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/ReportCropResponse"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function crop(Statistic\Crop\Report $request)
    {
        try {
            $dto = StatsDto::byArgs($request->all());

            $featureCrop = $this->featuresRepository->getBy('type_feature', Feature::TYPE_FEATURE_CROP);
            if(!$featureCrop){
                throw new \Exception('Not found a crop data');
            }
            $dto->feature = $featureCrop->id;

            return $this->successJsonMessage(
                $this->statisticReportService->reportCrop($dto)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}

