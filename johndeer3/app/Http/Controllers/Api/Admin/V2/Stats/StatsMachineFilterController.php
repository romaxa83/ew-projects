<?php

namespace App\Http\Controllers\Api\Admin\V2\Stats;

use App\DTO\Stats\StatsDto;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Statistic;
use App\Services\Statistics\StatisticFilterService;

class StatsMachineFilterController extends ApiController
{
    public function __construct(protected StatisticFilterService $service)
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/filter/country",
     *     tags={"Statistic filter"},
     *     summary="Получение стран для фильтров статистики (машин)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function country(Statistic\FilterCountry $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->machineCountryData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/filter/dealer",
     *     tags={"Statistic filter"},
     *     summary="Получение дилеров для фильтров статистики (машин)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="Фильтр по стране, \
     *               для указания всех стран передаем так - ?country=all, \
     *              если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function dealer(Statistic\FilterDealer $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->machineDealerData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/filter/eg",
     *     tags={"Statistic filter"},
     *     summary="Получение equipment group для фильтров статистики (машин)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="year", in="query", required=true,
     *          description="Год",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="country", in="query", required=true,
     *          description="фильтр по стране, \
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
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function eg(Statistic\FilterEg $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->machineEgData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v2/statistic/filter/md",
     *     tags={"Statistic filter"},
     *     summary="Получение model description для фильтров статистики (машин)",
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
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function md(Statistic\FilterMd $request)
    {
        try {
            return $this->successJsonMessage(
                $this->service->machineMdData(
                    StatsDto::byArgs($request->all())
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
