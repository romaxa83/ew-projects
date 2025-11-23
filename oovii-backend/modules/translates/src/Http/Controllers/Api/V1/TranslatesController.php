<?php

namespace WezomCms\Translates\Http\Controllers\Api\V1;

use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Models\Translation;
use WezomCms\Core\Traits\Hasher;
use WezomCms\Translates\Repositories\TranslateRepository;
use WezomCms\Translates\Services\TranslatesService;
use Illuminate\Http\Request;

class TranslatesController extends ApiController
{
    use Hasher;

    public function __construct(
        protected TranslateRepository $repo,
        protected TranslatesService $service,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/translates",
     *     tags={"Translates"},
     *     summary="Get all info pages",
     *
     *      @OA\Response(response="200", description="OK",
     *           @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(property="alias", title="Alias", description="Уникальный алиас для переводы", type="object",
     *                         @OA\Property(property="ru", title="ru", description="Ключ - локаль, значение - перевод для данной локали"),
     *                         @OA\Property(property="en", title="en", description="Ключ - локаль, значение - перевод для данной локали"),
     *                         @OA\Property(property="kk", title="kk", description="Ключ - локаль, значение - перевод для данной локали"),
     *                  ),
     *                  example={"button": {"ru": "button (ru)", "kk": "button (kk)", "en": "button (en)"}, "text": {"ru": "text (ru)", "kk": "text (kk)", "en": "text (en)"}}
     *             )
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function getTranslates()
    {
        try {
            $translates = $this->repo->getTranslates();

            return self::successJsonMessage($this->prettyTranslates($translates));

        } catch(\Exception $e){
            \Log::error($e->getMessage());
            return self::successJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    private function prettyTranslates($data): array
    {
        $sysLocale = array_flip(app('locales'));
        $temp = [];

        foreach ($sysLocale as $locale) {
            foreach($data ?? [] as $item){
                if($item->locale == $locale){
                    $temp[$item->key][$locale] = $item->text;
                } elseif (!isset($temp[$item->key][$locale])){
                    $temp[$item->key][$locale] = null;
                }
            }
        }

        return $temp;
    }

    /**
     * @OA\Post (
     *     path="/mobile/translates",
     *     tags={"Translates"},
     *     summary="Set or update translates",
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(property="alias", title="Alias", description="Уникальный алиас для переводы", type="object",
     *                         @OA\Property(property="ru", title="ru", description="Ключ - локаль, значение - перевод для данной локали"),
     *                         @OA\Property(property="en", title="en", description="Ключ - локаль, значение - перевод для данной локали"),
     *                         @OA\Property(property="kk", title="kk", description="Ключ - локаль, значение - перевод для данной локали"),
     *                  ),
     *                  example={"button": {"ru": "button (ru)", "kk": "button (kk)", "en": "button (en)"}, "text": {"ru": "text (ru)", "kk": "text (kk)", "en": "text (en)"}}
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="Хеш данных", example="99851568f2acb92ad1c36026dabf551d"),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function setTranslates(Request $request)
    {
        try {
            $this->service->fillOrUpdate($request->all());

            return self::successJsonMessage($this->hash($translates = $this->repo->getTranslates()));
        } catch(\Exception $e){
            \Log::error($e->getMessage());
            return self::successJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/translates/hash",
     *     tags={"Translates"},
     *     summary="Get hash for translates",
     *
     *      @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="Хеш данных", example="99851568f2acb92ad1c36026dabf551d"),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function getHash()
    {
        try {
            return self::successJsonMessage($this->hash($translates = $this->repo->getTranslates()));
        } catch(\Exception $e){
            \Log::error($e->getMessage());
            return self::successJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Delete (
     *     path="/mobile/translates/{alias?}",
     *     tags={"Translates"},
     *     summary="Delete translates",
     *     description="При удалении передаеться alias что бы удалить точечный перевод, без алиаса будут удалены ВСЕ!!! записи",
     *
     *      @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="Хеш данных", example="99851568f2acb92ad1c36026dabf551d"),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function delete($alias = null)
    {
        try {
            if($alias){
                $rows = $this->repo->getTranslateByKey($alias);
            } else {
                $rows = $this->repo->getTranslatesByNamespace(Translation::API_NAMESPACE);
            }

            $this->service->remove($rows);

            return self::successJsonMessage($this->hash($translates = $this->repo->getTranslates()));
        } catch(\Exception $e){
            \Log::error($e->getMessage());
            return self::successJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

