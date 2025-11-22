<?php

namespace App\Http\Controllers\Api\Broadcasting;

use App\Http\Resources\Broadcasting\AdminChannelResource;
use App\Http\Resources\Broadcasting\ChannelResource;
use App\Services\Broadcasting\ChannelService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BroadcastController
{
    /**
     *
     * @OA\Get(
     *     path="/api/broadcasts/channels",
     *     tags={"Broadcast channels"},
     *     summary="Get broadcasts channels for current users",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BroadCastChannelsResource")
     *     ),
     * )
     * @param Request $request
     * @param ChannelService $service
     * @return AnonymousResourceCollection
     */
    public function channels(Request $request, ChannelService $service): AnonymousResourceCollection
    {
        return ChannelResource::collection(
            $service->getChannelsForUser($request->user())
        );
    }

    /**
     *
     * @OA\Get(
     *     path="/api/broadcasts/back-office/channels",
     *     tags={"Broadcast channels for Backoffice"},
     *     summary="Get broadcasts channels for current users",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BroadCastChannelsResource")
     *     ),
     * )
     * @param Request $request
     * @param ChannelService $service
     * @return AnonymousResourceCollection
     */
    public function adminChannels(Request $request, ChannelService $service): AnonymousResourceCollection
    {
        return AdminChannelResource::collection(
            $service->getChannelsForAdmin($request->user())
        );
    }
}
