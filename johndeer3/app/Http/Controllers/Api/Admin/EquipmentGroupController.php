<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\EquipmentGroup\RelationSelfRequest;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Resources\JD\EquipmentGroupResource;
use App\Services\JD\EquipmentGroupService;

class EquipmentGroupController extends ApiController
{
    public function __construct(
        protected EquipmentGroupRepository $repo,
        protected EquipmentGroupService $service
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/api/admin/equipment-group/{equipmentGroup}/attach",
     *     tags={"Аdmin-panel"},
     *     summary="Привязка одних eg к другим для таблицы демо результата",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{equipmentGroup}", in="path", required=true,
     *          description="ID equipment group",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/RelationSelfRequest")
     *     ),
     *
     *     @OA\Response(response="200",
     *          description="Возвращаеться модель, к котороы привязали 'eg'",
     *          @OA\JsonContent(ref="#/components/schemas/EquipmentGroupResource")
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function attach(RelationSelfRequest $request, $id)
    {
        try {
            $model = $this->service->attachEgs(
                $this->repo->findBy('id', $id)
                , $request['egs'] ?? []
            );

            return $this->successJsonMessage(EquipmentGroupResource::make($model, ['som' => true]));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
