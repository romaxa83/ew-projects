<?php

namespace App\Resources\Custom;

use App\Models\Page\Page;

/**
 * @OA\Schema(type="object", title="Custom Page Resource",
 *     @OA\Property(property="lang_1", type="object",
 *         @OA\Property(property="name", type="string", example="title"),
 *         @OA\Property(property="text", type="string", example="text"),
 *     ),
 *     @OA\Property(property="lang_2", type="object",
 *         @OA\Property(property="name", type="string", example="title"),
 *         @OA\Property(property="text", type="string", example="text"),
 *     ),
 *     @OA\Property(property="lang_n", type="object",
 *         @OA\Property(property="name", type="string", example="title"),
 *         @OA\Property(property="text", type="string", example="text"),
 *     ),
 * )
 */

class CustomPageResource
{
    private $list = [];

    public function fill(Page $data, array $langs)
    {
        if(empty($langs)){
            $langs[] = \App::getLocale();
        }

        foreach ($langs as $lang){
            foreach ($data->translations ?? [] as $item){
                if(trim($lang) == $item->lang){
                    $this->list[$lang]['name'] = $item->name;
                    $this->list[$lang]['text'] = $item->text;
                }
            }
        }

        return $this->list;
    }
}
