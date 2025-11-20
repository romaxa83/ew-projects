<?php

namespace App\Resources\Page;

use App\Models\Page\Page;
use App\Models\Page\PageTranslation;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Page Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="alias", type="string", description="Alias страницы", example="agreement"),
 *     @OA\Property(property="translations", type="array", description="Переводы",
 *         @OA\Items(ref="#/components/schemas/PageTranslationResource")
 *     ),
 * )
 */

class PageResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Page $model */
        $model = $this;
        return [
            'id' => $model->id,
            'alias' => $model->alias,
            'translations' => PageTranslationResource::collection(
                $model->translations->filter(function(PageTranslation $model) {
                    if(request('lang')){
                        if(is_array(request('lang'))){
                            return in_array($model->lang, request('lang'));
                        }
                        return $model->lang == request('lang');
                    }
                    return true;
                })
            )
        ];
    }
}
