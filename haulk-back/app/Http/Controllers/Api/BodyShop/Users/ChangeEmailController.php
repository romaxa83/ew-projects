<?php

namespace App\Http\Controllers\Api\BodyShop\Users;

class ChangeEmailController extends \App\Http\Controllers\Api\Users\ChangeEmailController
{
    public const PUBLIC_CHANGE_EMAIL_PATH = '/body-shop/email-change';

    /**
     * @OA\Post(
     *     path="/api/body-shop/change-email/",
     *     tags={"Change email Body Shop"},
     *     summary="Create change email request",
     *     operationId="Create request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="new_email", in="query", description="New email", required=false,
     *          @OA\Schema( type="string", default="")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ChangeEmailResource")
     *     ),
     * )
     *
     * @OA\Delete(
     *     path="/api/body-shop/change-email/{changeEmailId}",
     *     tags={"Change email Body Shop"},
     *     summary="Delete change email request",
     *     operationId="Delete request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @OA\Post(
     *     path="/api/body-shop/change-email/confirm-email",
     *     tags={"Change email Body Shop"},
     *     summary="Confirm new email",
     *     operationId="Confirm new email",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(name="email", in="query", description="Email to confirm", required=true,
     *          @OA\Schema( type="string", default="")
     *     ),
     *     @OA\Parameter(name="token", in="query", description="Confirmation token", required=true,
     *          @OA\Schema( type="string", default="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @OA\Post(
     *     path="/api/body-shop/change-email/cancel-request",
     *     tags={"Change email Body Shop"},
     *     summary="Cancel change email request",
     *     operationId="Cancel request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(name="email", in="query", description="Email", required=true,
     *          @OA\Schema( type="string", default="")
     *     ),
     *     @OA\Parameter(name="token", in="query", description="Confirmation token", required=true,
     *          @OA\Schema( type="string", default="")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/api/body-shop/change-email/if-requested",
     *     tags={"Change email Body Shop"},
     *     summary="Check if current user has change email request",
     *     operationId="Check if current user have request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ChangeEmailResource")
     *     ),
     * )
     *
     */
}
