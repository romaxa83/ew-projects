<?php

namespace App\Http\Controllers\Api\Alerts;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Alerts\AlertsPaginatedResource;
use App\Models\Alerts\Alert;
use App\Models\Alerts\DeletedAlert;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AlertController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('alerts');

        $messages = Alert::query();

        if ($request->input('older_than')) {
            $messages = $messages->where('created_at', '<', date('Y-m-d H:i:s', $request->input('older_than')));

            $total = $messages->count();

            $messages = $messages->orderByDesc('id')
                ->take($request->input('per_page', 10))
                ->get();

            $hasOlder = $total > $messages->count();
        } elseif ($request->input('newer_than')) {
            $messages = $messages->where('created_at', '>', date('Y-m-d H:i:s', $request->input('newer_than')))
                ->orderBy('id')
                ->get()
                ->reverse()
                ->values();

            $hasOlder = false;
        } else {
            $messages = $messages->orderByDesc('id');

            $total = $messages->count();

            $messages = $messages->take($request->input('per_page', 10))
                ->get();

            $hasOlder = $total > $messages->count();
        }

        return AlertsPaginatedResource::collection($messages)
            ->additional(
                [
                    'meta' => [
                        'has_older' => $hasOlder
                    ]
                ]
            );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Alert $alert
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Alert $alert): JsonResponse
    {
        $this->authorize('alerts delete');

        if ($alert->isPersonal()) {
            $alert->delete();
        } else {
            $deleted = new DeletedAlert();
            $deleted->user_id = $request->user()->id;
            $deleted->alert_id = $alert->id;
            $deleted->save();
        }

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }
}

/**
 * @OA\Get(
 *     path="/api/alerts/",
 *     tags={"Alerts"},
 *     summary="Get alerts paginated list",
 *     operationId="Get alerts paginated list",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter (name="older_than", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter (name="newer_than", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AlertsPaginatedResource")
 *     ),
 * )
 *
 * @OA\Delete(
 *     path="/api/alerts/",
 *     tags={"Alerts"},
 *     summary="Delete alert",
 *     operationId="Delete alert",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Response(
 *         response=204,
 *         description="Successful operation",
 *     ),
 * )
 */
