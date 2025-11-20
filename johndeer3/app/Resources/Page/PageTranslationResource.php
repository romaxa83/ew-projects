<?php

namespace App\Resources\Page;

use App\Models\Page\Page;
use App\Models\Page\PageTranslation;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Page Translation Resource",
 *     @OA\Property(property="lang", type="string", description="Локаль", example="ua"),
 *     @OA\Property(property="title", type="string", description="Заголовок", example="Disclaimer"),
 *     @OA\Property(property="text", type="string", description="Текст", example="Declaro, com o presente, que sou o proprietário legal de todos os direitos relativos ao conteúdo carregado na aplicação de demonstração da John Deere. O mesmo "),
 * )
 */

class PageTranslationResource extends JsonResource
{

    public function toArray($request)
    {
        /** @var PageTranslation $model */
        $model = $this;

        return [
            'lang' => $model->lang,
            'title' => $model->name,
            'text' => $model->text
        ];
    }
}
