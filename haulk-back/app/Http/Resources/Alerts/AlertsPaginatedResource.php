<?php

namespace App\Http\Resources\Alerts;

use Illuminate\Http\Resources\Json\JsonResource;
use Lang;

class AlertsPaginatedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="AlertsPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="id", type="integer",),
     *                    @OA\Property(property="created_at", type="integer",),
     *                    @OA\Property(property="type", type="string",),
     *                    @OA\Property(property="message", type="string",),
     *                    @OA\Property(property="meta", type="object",),
     *                )
     *            }
     *        )
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->timestamp,
            'type' => $this->type,
            'message' => Lang::get(
                $this->message,
                $this->placeholders ?? [],
                $request->user()->language ?? 'en'
            ),
            'meta' => $this->meta,
        ];
    }
}
