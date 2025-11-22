<?php


namespace App\Http\Resources\Translates;


use App\Models\Translates\Translate;
use App\Traits\MultiLangResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslateMultiLangResource extends JsonResource
{
    use MultiLangResource;

    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="TranslateMultiLang",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="Translate data",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "key"},
     *                          @OA\Property(property="id", type="integer", description="Translate id"),
     *                          @OA\Property(property="key", type="string", description="Translate key"),
     *                          @OA\Property(
     *                              property="language_slug",
     *                              description="Language slug en,ru,es, etc..",
     *                              type="object",
     *                              allOf={
     *                                  @OA\Schema(
     *                                      required={"text"},
     *                                      @OA\Property(property="text", type="string", description="Text of translate"),
     *                                  )
     *                              }
     *                          )
     *                      )
     *              }
     *           ),
     * )
     *
     * @OA\Schema(
     *   schema="TranslateMultiLangRaw",
     *   type="object",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "key"},
     *                          @OA\Property(property="id", type="integer", description="Translate id"),
     *                          @OA\Property(property="key", type="string", description="Translate key"),
     *                          @OA\Property(
     *                              property="language_slug",
     *                              description="Language slug en,ru,es, etc..",
     *                              type="object",
     *                              allOf={
     *                                  @OA\Schema(
     *                                      required={"text"},
     *                                      @OA\Property(property="text", type="string", description="Text of translate"),
     *                                  )
     *                              }
     *                          )
     *                      )
     *              }
     * )
     *
     * @OA\Schema(
     *   schema="TranslatePaginate",
     *   @OA\Property(
     *      property="data",
     *      description="Translate paginated list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/TranslateMultiLangRaw")
     *   ),
     *   @OA\Property(
     *      property="links",
     *      ref="#/components/schemas/PaginationLinks",
     *   ),
     *   @OA\Property(
     *      property="meta",
     *      ref="#/components/schemas/PaginationMeta",
     *   ),
     * )
     */
    public function toArray($request)
    {
        /** @var Translate $translate */
        $translate = $this;
        $data = [
            'id' => $translate->id,
            'key' => $translate->key,
        ];
        return $this->mergeMultiLangData($data, $translate);
    }
}
