<?php

namespace WezomCms\Core\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Log;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Http\Resources\V1\SettingResource;

class SettingsController extends ApiController
{
    /**
     * @OA\Get (
     *     path="/mobile/settings",
     *     tags={"Settings"},
     *     summary="Get a list of settings",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/SettingResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $settings = settings();
            $sharedSettings = collect(config('cms.core.api.shared_settings', []))
                ->map(fn (string $key) => [
                    'key' => $key,
                    'value' => $settings->get($key)
                ]);

            return self::successJsonMessage(SettingResource::collection($sharedSettings));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }
}
