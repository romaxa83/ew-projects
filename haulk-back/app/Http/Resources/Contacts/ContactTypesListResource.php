<?php

namespace App\Http\Resources\Contacts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactTypesListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="ContactTypesListResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Contact type data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"id", "title"},
     *                        @OA\Property(property="id", type="integer", description="Contact type id"),
     *                        @OA\Property(property="title", type="string", description="Contact type name"),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'title' => $this['title'],
        ];
    }
}
