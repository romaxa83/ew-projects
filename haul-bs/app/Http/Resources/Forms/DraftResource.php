<?php

namespace App\Http\Resources\Forms;

use App\Models\Forms\Draft;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="DraftResource", type="object",
 *     @OA\Property(property="data", type="object", description="Draft data", allOf={
 *          @OA\Schema(required={"id", "full_name", "email", "phone","status","security_level"},
 *              @OA\Property(property="some_filed", type="mixed", description="Some field saved data"),
 *          )
 *       }
 *    ),
 * )
 *
 * @mixin Draft
 */

class DraftResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Draft $draft */
        $draft = $this;

        return $draft->body;
    }
}

