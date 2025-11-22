<?php

namespace App\Http\Controllers\V1\Saas\TextBlocks;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\TextBlocks\IndexRequest;
use App\Http\Requests\Saas\TextBlocks\StoreRequest;
use App\Http\Requests\Saas\TextBlocks\UpdateRequest;
use App\Http\Resources\Saas\TextBlocks\TextBlockGroupResource;
use App\Http\Resources\Saas\TextBlocks\TextBlockRenderResource;
use App\Http\Resources\Saas\TextBlocks\TextBlockResource;
use App\Http\Resources\Saas\TextBlocks\TextBlockScopeResource;
use App\Models\Admins\Admin;
use App\Models\Saas\TextBlock;
use App\Models\Users\User;
use App\Services\Saas\TextBlocks\TextBlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use stringEncode\Exception;

class TextBlockController extends ApiController
{

    /**
     * @param IndexRequest $request
     * @param TextBlockService $textBlockService
     * @return AnonymousResourceCollection
     * @OA\Get(
     *     path="/v1/saas/text-blocks",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Get text blocks list",
     *     operationId="Get text blocks",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Translates per page", required=false,
     *          @OA\Schema(type="integer", default="50")
     *     ),
     *     @OA\Parameter(name="query", in="query", description="Data for filter", required=false,
     *          @OA\Schema(type="string", default="Text")
     *     ),
     *     @OA\Parameter(name="group", in="query", description="Filter on the group", required=false,
     *          @OA\Schema(type="string", default="Text")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlocksList")
     *     ),
     * )
     */
    public function index(IndexRequest $request, TextBlockService $textBlockService): AnonymousResourceCollection
    {
        return TextBlockResource::collection(
            $textBlockService->getList($request->dto())
        );
    }

    /**
     * @param StoreRequest $request
     * @param TextBlockService $textBlockService
     * @return JsonResponse
     * @throws \Throwable
     * @OA\Post (
     *     path="/v1/saas/text-blocks",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Create new text block",
     *     operationId="Create text blocks",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="group", in="query", description="Group by text (page)", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="block", in="query", description="Block name (in page)", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="en", in="query", description="English text for block", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="es", in="query", description="Spanish text for block", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="ru", in="query", description="Russian text for block", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlock")
     *     ),
     * )
     */
    public function store(StoreRequest $request, TextBlockService $textBlockService): JsonResponse
    {
        try {
            $textBlock = $textBlockService->saveTextBlock($request->dto());
        } catch (Exception $exception) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return TextBlockResource::make($textBlock)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param TextBlock $textBlock
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/v1/saas/text-blocks/{textBlockId}",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Get one text block",
     *     operationId="Get text block",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Id for text block", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlock")
     *     ),
     * )
     */
    public function show(TextBlock $textBlock): JsonResponse
    {
        return TextBlockResource::make($textBlock)->response();
    }

    /**
     * @param TextBlock $textBlock
     * @param UpdateRequest $request
     * @param TextBlockService $textBlockService
     * @return JsonResponse
     * @throws \Throwable
     *
     * @OA\Put (
     *     path="/v1/saas/text-blocks/{textBlockId}",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Update text block",
     *     operationId="Update text block",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Id for text block", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="group", in="query", description="Group by text (page)", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="block", in="query", description="Block name (in page)", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="en", in="query", description="English text for block", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="es", in="query", description="Spanish text for block", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="ru", in="query", description="Russian text for block", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlock")
     *     ),
     *     @OA\Response(response=500, description="Save error")
     * )
     */
    public function update(TextBlock $textBlock, UpdateRequest $request, TextBlockService $textBlockService): JsonResponse
    {
        try {
            $textBlock = $textBlockService->saveTextBlock($request->dto(), $textBlock);
        } catch (Exception $exception) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return TextBlockResource::make($textBlock)->response();
    }

    /**
     * @param TextBlock $textBlock
     * @return JsonResponse
     * @throws \Throwable
     *
     * @OA\Delete  (
     *     path="/v1/saas/text-blocks/{textBlockId}",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Delete text block",
     *     operationId="Delete text block",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Id for text block", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function destroy(TextBlock $textBlock): JsonResponse
    {
        $textBlock->delete();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/v1/saas/text-blocks/groups",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Get text blocks groups",
     *     operationId="Get text blocks groups",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlockGroups")
     *     ),
     * )
     */
    public function groups(): JsonResponse
    {
        return TextBlockGroupResource::make(['groups' => TextBlock::TB_GROUPS])->response();
    }

    /**
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/v1/saas/text-blocks/scopes",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Get text blocks scopes",
     *     operationId="Get text blocks scopes",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlockScopes")
     *     ),
     * )
     */
    public function scopes(): JsonResponse
    {
        return TextBlockScopeResource::make(['scopes' => TextBlock::TB_SCOPES])->response();
    }

    /**
     *
     * @OA\Get (
     *     path="/v1/saas/text-blocks/render",
     *     tags={"V1 Saas Text Blocks"},
     *     summary="Get text blocks render",
     *     operationId="Get text blocks render",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TextBlockRender")
     *     ),
     * )
     * @param Request $request
     * @param TextBlockService $textBlockService
     * @return TextBlockRenderResource
     */
    public function render(Request $request, TextBlockService $textBlockService): TextBlockRenderResource
    {
        /**@var Admin|User $user*/
        $user = $request->user(User::GUARD) ? $request->user(User::GUARD) : $request->user(Admin::GUARD);

        return TextBlockRenderResource::make([
            'list' => $textBlockService->getRenderTextBlocks($user)->toArray(),
            'language' => $user ? $user->language : 'en'
        ]);
    }
}
