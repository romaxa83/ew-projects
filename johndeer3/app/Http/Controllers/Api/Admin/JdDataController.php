<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Admin\RequestUpdateDealer;
use App\Models\JD\Dealer;
use App\Repositories\JD\DealersRepository;
use App\Resources\JD\DealerResource;
use App\Services\JD\DealerService;

class JdDataController extends ApiController
{
    public function __construct(
        protected DealersRepository $dealersRepository,
        protected DealerService $dealerService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/dealers",
     *     tags = {"Dealers"},
     *     summary="Получение списка дилеров (загружаются с BOED)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Список дилеров",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/DealerResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function dealers()
    {
        try {
            return $this->successJsonMessage(
                DealerResource::collection(
                    $this->dealersRepository->getAll(['nationality', 'users'])
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/dealers/edit/{dealer}",
     *     tags = {"Dealers"},
     *     summary="Редактирование дилера",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{dealer}", in="path", required=true,
     *          description="ID дилера",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/RequestUpdateDealer")
     *     ),
     *
     *     @OA\Response(response="200", description="Отредактированый дилер",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/DealerResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function dealersEdit(RequestUpdateDealer $request, Dealer $dealer)
    {
        try {
            $model = $this->dealerService->edit($request->all(), $dealer);

            return $this->successJsonMessage(
                DealerResource::make($model)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
