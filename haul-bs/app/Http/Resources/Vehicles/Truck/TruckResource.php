<?php

namespace App\Http\Resources\Vehicles\Truck;

use App\Http\Resources\Vehicles\VehicleResource;
use App\Models\Vehicles\Truck;

/**
 * @OA\Schema(schema="TruckResource", type="object",
 *     @OA\Property(property="data", type="object", description="Truck data", allOf={
 *         @OA\Schema(required={"id", "vin", "unit_number", "year", "make", "model", "type"},
 *         @OA\Property(property="id", type="integer", example="3"),
 *         @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *         @OA\Property(property="unit_number", type="string", example="SL56"),
 *         @OA\Property(property="license_plate", type="string", example="TK348OKT"),
 *         @OA\Property(property="temporary_plate", type="string", example="651133T"),
 *         @OA\Property(property="make", type="string", example="FORD"),
 *         @OA\Property(property="model", type="string", example="F-350"),
 *         @OA\Property(property="year", type="string", example="2022"),
 *         @OA\Property(property="type", type="integer", example="8"),
 *         @OA\Property(property="owner", ref="#/components/schemas/CustomerShortListResource"),
 *         @OA\Property(property="tags", type="array", description="Truck tags",
 *             @OA\Items(ref="#/components/schemas/TagRawShort")
 *         ),
 *         @OA\Property(property="notes", type="string", example="some text"),
 *         @OA\Property(property="company_name", type="string", example="GIG Logistic"),
 *         @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is truck has related open orders"),
 *         @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is truck has related deleted orders"),
 *         @OA\Property(property="attachments", type="array", description="Vehicle attachments",
 *             @OA\Items(ref="#/components/schemas/FileRaw")
 *         ),
 *         @OA\Property(property="color", type="string", example="Black"),
 *         @OA\Property(property="gvwr", type="number", example="100.9"),
 *     )}
 * ),
 * )
 *
 * @mixin Truck
 */

class TruckResource extends VehicleResource
{}
