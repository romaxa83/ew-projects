<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Country;
use App\Repositories\JD\CountryRepository;
use App\Repositories\NationalityRepository;
use App\Resources\Country\CountryResource;
use App\Resources\Country\NationalityResource;
use App\Services\Catalog\CountryService;
use Illuminate\Http\Request;

class CountryController extends ApiController
{
    public function __construct(
        protected CountryRepository $countryRepository,
        protected CountryService $service,
        protected NationalityRepository $nationalityRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/countries",
     *     tags={"Аdmin-panel"},
     *     summary="Получить страны",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="perPage", in="query", required=false,
     *          description="Значений на страницу",
     *          @OA\Schema(type="integer", example="15")
     *     ),
     *     @OA\Parameter(name="isActive", in="query", required=false,
     *          description="Только активированые",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(name="paginator", in="query", required=false,
     *          description="Возврат данных с пагинацией",
     *          @OA\Schema(type="boolean", example=true, default=true)
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/CountryCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index(Request $request)
    {
        try {
            $countries = $this->countryRepository->getAllWrap(
                [],
                $request->all(),
                $this->orderDataForQuery(),
                $request["isActive"]
            );

            return CountryResource::collection($countries);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/country-toggle-active/{country}",
     *     tags={"Аdmin-panel"},
     *     summary="Переключить активность страны",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{country}", in="path", required=true,
     *          description="ID country",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200",
     *          description="Возвращаются данные страны, которая была изменена",
     *          @OA\JsonContent(ref="#/components/schemas/CountryResource")
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function toggleActive(Country $country)
    {
        try {

            return $this->successJsonMessage(CountryResource::make(
                $this->service->toggleActive($country)
            ));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/nationalities",
     *     tags={"Аdmin-panel"},
     *     summary="Получить национальности",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="perPage", in="query", required=false,
     *          description="Значений на страницу",
     *          @OA\Schema(type="integer", example="15")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/NationalityCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function nationalities(Request $request)
    {
        try {
            $models = $this->nationalityRepository->getAllPaginator(
                [],
                $request->all(),
                $this->orderDataForQuery(),
            );

            return NationalityResource::collection($models);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}

