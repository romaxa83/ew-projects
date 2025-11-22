<?php

namespace App\Http\Resources\Vehicles\Truck;

use App\Http\Resources\Vehicles\VehiclePaginationResource;
use App\Models\Vehicles\Truck;

/**
 * @OA\Schema(schema="TruckPaginationRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "vin", "unit_number", "year", "make", "model", "type"},
 *         @OA\Property(property="id", type="integer", example="3"),
 *         @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *         @OA\Property(property="unit_number", type="string", example="SL56"),
 *         @OA\Property(property="license_plate", type="string", example="TK348OKT"),
 *         @OA\Property(property="temporary_plate", type="string", example="651133T"),
 *         @OA\Property(property="make", type="string", example="FORD"),
 *         @OA\Property(property="model", type="string", example="F-350"),
 *         @OA\Property(property="year", type="string", example="2022"),
 *         @OA\Property(property="type", type="integer", example="8"),
 *         @OA\Property(property="owner_name", type="string", example="John Doe"),
 *         @OA\Property(property="customer_id", type="integer", example="2"),
 *         @OA\Property(property="tags", type="array", description="Truck tags",
 *             @OA\Items(ref="#/components/schemas/TagRawShort")
 *         ),
 *         @OA\Property(property="company_name", type="string", example="GIG Logistic"),
 *         @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is truck has related open orders"),
 *         @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is truck has related deleted orders"),
 *         @OA\Property(property="comments_count", type="int", example="5"),
 *         @OA\Property(property="color", type="string", example="Black"),
 *         @OA\Property(property="gvwr", type="number", example="100.9"),
 *     )}
 * )
 *
 * @OA\Schema(schema="TruckPaginationResource",
 *     @OA\Property(property="data", description="Truck paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/TruckPaginationRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin Truck
 */
class TruckPaginationResource extends VehiclePaginationResource
{}
