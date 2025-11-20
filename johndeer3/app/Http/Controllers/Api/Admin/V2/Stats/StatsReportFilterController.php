<?php

namespace App\Http\Controllers\Api\Admin\V2\Stats;

use App\DTO\Stats\StatsDto;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Statistic\RequestReportCount;
use App\Models\Report\Feature\Feature;
use App\Repositories\Feature\FeatureRepository;
use App\Services\Statistics\StatisticFilterService;
use App\Http\Request\Statistic;

class StatsReportFilterController extends ApiController
{
    public function __construct(
        protected StatisticFilterService $service,
        protected FeatureRepository $featuresRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/reports/filter/status",
     *     tags={"Statistic filter"},
     *     summary="Получение статусов для фильтров статистики (отчетов)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=false,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function status(Statistic\Report\FilterStatus $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportStatusData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/reports/filter/country",
     *     tags={"Statistic filter"},
     *     summary="Получение статусов для фильтров статистики (отчетов)",
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
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function country(Statistic\Report\FilterCountry $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportCountryData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/reports/filter/dealer",
     *     tags={"Statistic filter"},
     *     summary="Получение дилеров для фильтров статистики (отчетов)",
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
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function dealer(Statistic\Report\FilterDealer $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportDealerData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/reports/filter/eg",
     *     tags={"Statistic filter"},
     *     summary="Получение equipment group для фильтров статистики (отчетов)",
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
     *     @OA\Parameter(name="onlyCombine", in="query", required=false,
     *          description="Возвращать equipment group только по комбайнов",
     *          @OA\Schema(type="boolean", example=true, default=false)
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function eg(Statistic\Report\FilterEg $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportEgData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/reports/filter/md",
     *     tags={"Statistic filter"},
     *     summary="Получение model description для фильтров статистики (отчетов)",
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
     *          для указания всех eg передаем так - ?eg=all \
     *          если нужно передать несколько значений, то так - ?eg[]=2&eg[]=4",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function md(Statistic\Report\FilterMd $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportMdData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/type/filter",
     *     tags={"Statistic filter"},
     *     summary="Получение type для фильтров статистики по type для model description",
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
     *          для указания всех eg передаем так - ?eg=all \
     *          если нужно передать несколько значений, то так - ?eg[]=2&eg[]=4",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id\
     *          для указания всех md передаем так - ?md=all \
     *          если нужно передать несколько значений, то так - ?md[]=44&md[]=91",
     *          @OA\Schema(type="integer", example="44")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function type(RequestReportCount $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportTypeData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/size/filter",
     *     tags={"Statistic filter"},
     *     summary="Получение size для фильтров статистики по size",
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
     *          для указания всех eg передаем так - ?eg=all \
     *          если нужно передать несколько значений, то так - ?eg[]=2&eg[]=4",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id\
     *          для указания всех md передаем так - ?md=all \
     *          если нужно передать несколько значений, то так - ?md[]=44&md[]=91",
     *          @OA\Schema(type="integer", example="44")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function size(RequestReportCount $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->reportSizeData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/crop/filter",
     *     tags={"Statistic filter"},
     *     summary="Получение crop (вид культур) для фильтров статистики по crop",
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
     *          для указания всех eg передаем так - ?eg=all \
     *          если нужно передать несколько значений, то так - ?eg[]=2&eg[]=4",
     *          @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(name="md", in="query", required=true,
     *          description="Фильтр по model description, передается id\
     *          для указания всех md передаем так - ?md=all \
     *          если нужно передать несколько значений, то так - ?md[]=44&md[]=91",
     *          @OA\Schema(type="integer", example="44")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function crop(RequestReportCount $request)
    {
        try {
            $featureCrop = $this->featuresRepository->getBy('type_feature', Feature::TYPE_FEATURE_CROP);
            if(!$featureCrop){
                throw new \Exception('Not found a crop data');
            }
            $dto = StatsDto::byArgs($request->all());
            $dto->feature = $featureCrop->id;

            return $this->successJsonMessage(
                $this->service->reportCropData($dto)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
