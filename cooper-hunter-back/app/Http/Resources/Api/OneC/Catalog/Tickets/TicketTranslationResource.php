<?php

namespace App\Http\Resources\Api\OneC\Catalog\Tickets;

use App\Models\Catalog\Tickets\TicketTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketTranslation
 */
class TicketTranslationResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'language' => $this->language,
        ];
    }
}
