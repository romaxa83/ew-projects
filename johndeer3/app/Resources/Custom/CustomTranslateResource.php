<?php

namespace App\Resources\Custom;

use App\Repositories\LanguageRepository;

/**
 * @OA\Schema(type="object", title="Custom Translate Resource",
 *     @OA\Property(property="lang_1", type="object",
 *         @OA\Property(property="key_1", type="string", example="value_1",
 *             description="ключ - алиас перевода, значение - перевод для этой локали"
 *         ),
 *         @OA\Property(property="key_2", type="string", example="value_2",
 *             description="ключ - алиас перевода, значение - перевод для этой локали"
 *         ),
 *          @OA\Property(property="key_n", type="string", example="value_n",
 *             description="ключ - алиас перевода, значение - перевод для этой локали"
 *         ),
 *     ),
 *     @OA\Property(property="lang_2", type="object",
 *         @OA\Property(property="key_1", type="string", example="value_1",
 *             description="ключ - алиас перевода, значение - перевод для этой локали"
 *         ),
 *         @OA\Property(property="key_2", type="string", example="value_2",
 *             description="ключ - алиас перевода, значение - перевод для этой локали"
 *         ),
 *          @OA\Property(property="key_n", type="string", example="value_n",
 *             description="ключ - алиас перевода, значение - перевод для этой локали"
 *         ),
 *     ),
 * )
 */

class CustomTranslateResource
{
    private $list = [];

    public function fill($data)
    {
        $langRepository = app(LanguageRepository::class);
        $langs = $langRepository->getForSelect();

        if(is_array($data) && !empty($data)){
            foreach ($data as $item){
                if(array_key_exists($item['lang'], $langs)){
                    $this->list[$item['lang']][$item['alias']] = $item['text'];
                }
            }
        }

        return $this->list;
    }
}

