<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Page\PageUpdateRequest;
use App\Models\Page\Page;
use App\Repositories\LanguageRepository;
use App\Repositories\PageRepository;
use App\Resources\Custom\CustomPageResource;
use App\Resources\Page\PageResource;
use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends ApiController
{
    public function __construct(
        protected PageService $service,
        protected PageRepository $repo,
        protected LanguageRepository $languageRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/pages",
     *     tags = {"Page"},
     *     summary="Список все страниц",
     *
     *     @OA\Parameter(name="alias", in="query", required=false,
     *          description="Alias страницы",
     *          @OA\Schema(type="string", example="agreement", enum={"agreement", "disclaimer", "private-policy"})
     *     ),
     *     @OA\Parameter(name="lang", in="query", required=false,
     *          description="На каких языках возвращать переводы, если нужно передать несколько языков то так - ?lang[]=en&lang[]=ua",
     *          @OA\Schema(type="string", example="ua",
     *              enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
     *          )
     *     ),
     *     @OA\Parameter(name="paginator", in="query", required=false,
     *          description="Возврат данных с пагинацией",
     *          @OA\Schema(type="boolean", example=true, default="false")
     *     ),
     *
     *     @OA\Response(response="200", description="Page",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/PageResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index(Request $request)
    {
        try {
            $filter = $request->all();
            $filter['paginator'] = $request['paginator'] ?? false;

            $models = $this->repo->getAllWrap(
                ['translations'],
                $request->all(),
                $this->orderDataForQuery(),
                true
            );

            return $this->successJsonMessage(PageResource::collection($models));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/admin/page/edit/{alias}",
     *     tags = {"Page"},
     *     summary="Редактирование страниц",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{alias}", in="path", required=true,
     *          description="Alias страницы",
     *          @OA\Schema(type="string", example="disclaimer")
     *     ),
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/PageUpdateRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Page",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/CustomPageResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function update(PageUpdateRequest $request, string $alias)
    {
        try {
            $model = $this->service->update(
                $this->repo->findBy('alias',$alias, ['translations']),
                $request->all()
            );

            $dto = resolve(CustomPageResource::class)->fill(
                $model,
                array_flip($this->languageRepository->getForSelect())
            );

            return $this->successJsonMessage($dto);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/admin/page/alias-list",
     *     tags = {"Page"},
     *     summary="Алиасы страниц",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function getPageAlias()
    {
        return $this->successJsonMessage(Page::aliasList());
    }
}
