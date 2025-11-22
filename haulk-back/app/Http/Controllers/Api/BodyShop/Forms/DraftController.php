<?php

namespace App\Http\Controllers\Api\BodyShop\Forms;

class DraftController extends \App\Http\Controllers\Api\Forms\DraftController
{
    /**
     * @OA\Get(path="/api/body-shop/forms/drafts/{path}", tags={"Drafts Body Shop"}, summary="Get draft attributes for some form", operationId="Get drafted data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="path", in="path", description="Draft path", required=true,
     *          @OA\Schema(type="string", example="super_unique_slug")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Draft")
     *     ),
     *     @OA\Response(response=404, description="Draft not found"),
     * )
     *
     * @OA\Post(path="/api/body-shop/forms/drafts/{path}", tags={"Drafts Body Shop"}, summary="Store draft data from form", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="path", in="path", description="Draft type", required=true,
     *          @OA\Schema(type="string", example="contact")
     *     ),
     *     @OA\Parameter(name="field1", in="query", description="Some filed attribute for draft", required=true,
     *          @OA\Schema(type="string", example="Text for field1")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     *
     * @OA\Delete(path="/api/body-shop/forms/drafts/{path}", tags={"Drafts Body Shop"}, summary="Delete draft for form", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="path", in="path", description="Draft type", required=true,
     *          @OA\Schema(type="string", example="contact")
     *     ),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
}
