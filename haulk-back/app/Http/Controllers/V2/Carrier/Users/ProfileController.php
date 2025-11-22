<?php

namespace App\Http\Controllers\V2\Carrier\Users;

use App\Http\Requests\V2\Users\ProfileRequest;
use App\Http\Resources\Users\ProfileResource;
use App\Services\Events\EventService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ProfileController extends \App\Http\Controllers\V1\Carrier\Users\ProfileController
{
    /**
     * @param ProfileRequest $request
     * @return ProfileResource|JsonResponse
     *
     * @OA\Put(path="/v2/carrier/profile", tags={"Profile V2"},summary="Update user info", operationId="Update user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="first_name", in="query", description="User first name", required=true,
     *          @OA\Schema(type="string", default="Vlad",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="User last name", required=true,
     *          @OA\Schema(type="string", default="Chernenko",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="User phone", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Phone extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Additional phone", required=false,
     *          @OA\Schema(type="array", description="User aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Profile")
     *     ),
     * )
     * @throws AuthorizationException
     */
    public function updateV2(ProfileRequest $request)
    {
        $this->authorize('profile update');

        $event = EventService::users($this->user)
            ->setLoggedUser($this->user);

        $this->user->fill($request->validated());
        if ($this->user->save()) {
            $event->update();

            return new ProfileResource($this->user);
        }
        return $this->makeErrorResponse(null, 500);
    }
}
