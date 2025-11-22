<?php


namespace App\Http\Resources;


use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="Language",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="Language data",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "name", "slug","default"},
     *                          @OA\Property(property="id", type="integer", description="Language id"),
     *                          @OA\Property(property="name", type="string", description="Language name"),
     *                          @OA\Property(property="slug", type="string", description="Language slug"),
     *                          @OA\Property(property="default", type="boolean", description="Language is default?"),
     *                      )
     *           }
     *           ),
     * )
     *
     * @OA\Schema(
     *   schema="LanguageRaw",
     *   type="object",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "name", "slug","default"},
     *                          @OA\Property(property="id", type="integer", description="Language id"),
     *                          @OA\Property(property="name", type="string", description="Language name"),
     *                          @OA\Property(property="slug", type="string", description="Language slug"),
     *                          @OA\Property(property="default", type="boolean", description="Language is default?"),
     *                      )
     *           }
     * )
     *
     * @OA\Schema(
     *   schema="LanguagesList",
     *   @OA\Property(
     *      property="data",
     *      description="LanguageService list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/LanguageRaw")
     *   ),
     * )
     */
    public function toArray($request)
    {
        /** @var Language $language */
        $language = $this;
        return [
            'id' => $language->id,
            'name' => $language->name,
            'slug' => $language->slug,
            'default' => $language->default ? true : false,
        ];
    }
}