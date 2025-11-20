<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Api\ApiController;
use App\Models\Version;

class HashController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/hash/{type}",
     *     tags={"Catalog"},
     *     summary="Получение хеша данных",
     *     description="Получение хеша данных, для МП чтоб контролировать изменения данных",
     *
     *     @OA\Parameter(name="{type}", in="path", required=true,
     *          description="type - данные по которам высчитывается хеш, один из вариантов -  clients, dealers, equipment-groups, manufacturers, model-descriptions, pages",
     *          @OA\Schema(type="string", example="clients", enum={"clients", "dealers", "equipment-groups", "manufacturers", "model-descriptions", "pages"})
     *     ),
     *
     *     @OA\Response(response="200", description="Хеш",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="string", description="Хеш", example="d2f757b4db9c3a4eb589dfab0ccbc5e70"),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function hash($type)
    {
        try {
            return $this->successJsonMessage(
                Version::getActualHash(
                    $this->convertType($type)
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    // todo убрать после того как изменят на фронте
    private function convertType($type)
    {
        if($type == 'model-descriptions'){
            return Version::MODEL_DESCRIPTION;
        }
        if($type == 'equipment-groups'){
            return Version::EQUIPMENT_GROUP;
        }
        return $type;
    }
}
