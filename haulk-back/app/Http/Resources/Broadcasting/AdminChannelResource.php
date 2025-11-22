<?php

namespace App\Http\Resources\Broadcasting;

use App\Broadcasting\Channels\AdminChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AdminChannel $resource
 */
class AdminChannelResource extends JsonResource
{
    /**
     *
     * @OA\Schema(schema="BroadCastAdminChannelsResource",
     *    @OA\Property(property="data", description="Broadcast admin channels", type="array",
     *        @OA\Items(ref="#/components/schemas/BroadCastAdminChannelResource")
     *    ),
     * )
     *
     * @OA\Schema(schema="BroadCastAdminChannelResource", type="object",
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
            'name' => $channel::getNameForAdmin($request->user()),
            'prefix' => $channel->getPrefix(),
            'events' => $channel->getEvents(),
        ];
    }
}
