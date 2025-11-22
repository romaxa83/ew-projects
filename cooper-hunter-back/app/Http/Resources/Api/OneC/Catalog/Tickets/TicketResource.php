<?php

namespace App\Http\Resources\Api\OneC\Catalog\Tickets;

use App\Http\Resources\Api\OneC\Orders\OrderCategories\OrderCategoryResource;
use App\Http\Resources\Api\OneC\Orders\OrderResource;
use App\Models\Catalog\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ticket
 */
class TicketResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'code' => $this->code,
            'status' => $this->status,
            'comment' => $this->comment,
            'translations' => TicketTranslationResource::collection($this->translations),
            'order_parts' => $this->order_parts,
            'order_parts_relation' => OrderCategoryResource::collection($this->orderPartsRelation),
            'orders' => OrderResource::collection($this->orders),
            'case_id' => $this->case_id,
        ];
    }
}
