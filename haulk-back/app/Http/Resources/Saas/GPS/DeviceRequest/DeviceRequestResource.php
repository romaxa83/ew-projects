<?php

namespace App\Http\Resources\Saas\GPS\DeviceRequest;

use App\Http\Resources\Users\UserMiniResource;
use App\Models\Saas\GPS\DeviceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeviceRequest
 */
class DeviceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
//        dd($this->source);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'user' => UserMiniResource::make($this->user),
            'company' => $this->company
                ? [
                    'name' => $this->company->name,
                    'id' => $this->company->id,
                ]
                : null,
            'qty' => $this->qty,
            'created_at' => $this->created_at->timestamp,
            'closed_at' => $this->closed_at ? $this->closed_at->timestamp : null,
            'comment' => $this->comment,
            'source' => $this->source
        ];
    }
}

/**
 * @OA\Schema(schema="DeviceRequestRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="status", type="string", enum={"new", "in_work", "closed"}),
 *             @OA\Property(property="qty", type="integer"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="closed_at", type="integer"),
 *             @OA\Property(property="comment", type="string"),
 *             @OA\Property(property="user", ref="#/components/schemas/UserMini"),
 *             @OA\Property(property="company", ref="#/components/schemas/CompanyForDeviceResource"),
 *             @OA\Property(property="source", type="string", enum={"crm", "backoffice"}),
 *         )
 *     }
 * )
 *
 *
 * @OA\Schema(schema="DeviceRequestResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/DeviceRequestRawResource")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="DeviceRequestPaginatedResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/DeviceRequestRawResource")
 *     ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */

