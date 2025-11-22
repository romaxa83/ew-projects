<?php

namespace App\Http\Resources\Broadcasting;

use App\Broadcasting\Channels\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Channel $resource
 */
class ChannelResource extends JsonResource
{
    /**
     *
     * @OA\Schema(schema="BroadCastChannelsResource",
     *    @OA\Property(property="data", description="Broadcast channels", type="array",
     *        @OA\Items(ref="#/components/schemas/BroadCastChannelResource")
     *    ),
     * )
     *
     * @OA\Schema(schema="BroadCastChannelResource", type="object",
     *     allOf={
     *      @OA\Schema(
     *          @OA\Property(property="name", type="string", description="Name of broadcast channel"),
     *          @OA\Property(property="prefix", type="string", description="prefix for channel name", example="private-"),
     *          @OA\Property(property="events", type="array", description="All allowed events",
     *              @OA\Items(type="string"),
     *          ),
     *     )}
     * )
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $channel = $this->resource;

        return [
            'name' => $channel::getNameForUser($request->user()),
            'prefix' => $channel->getPrefix(),
            'events' => $channel->getEvents(),
        ];
    }
}
