<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Enums\Inventories\InventoryPackageType;
use App\Enums\Inventories\Transaction\PaymentMethod;
use App\Foundations\Enums\EnumHelper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Common\SimpleDataResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends ApiController
{
    public function __construct()
    {}

    /**
     * @OA\Get(
     *     path="/v1/inventories/package-types",
     *     tags={"Inventory catalog"},
     *     summary="Get package type list for inventory",
     *     operationId="GetPackageTypeListForInventory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\Response(response=200, description="Package type data",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleDataResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     *
     */
    public function packageType(): AnonymousResourceCollection
    {
        $res = [];
        $data = EnumHelper::valuesWithLabel(InventoryPackageType::class);

        foreach ($data as $key => $title){
            $res[] = [
                'key' => $key,
                'title' => $title,
            ];
        }
        return SimpleDataResource::collection($res);
    }
}
