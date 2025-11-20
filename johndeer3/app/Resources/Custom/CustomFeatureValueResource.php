<?php

namespace App\Resources\Custom;

use App\Models\Report\Feature\FeatureValue;
use App\Models\Report\Feature\FeatureValueTranslates;

/**
 * @OA\Schema(type="object", title="CustomFeatureValueResource",
 *     @OA\Property(property="id", type="integer", description="ID value", example=1),
 *     @OA\Property(property="ru", type="string", example="озимая пшеница",
 *           description="ключ - локаль, значение - название значения харак. для этой локали"
 *     ),
 *     @OA\Property(property="ua", type="string", example="озима пшениця",
 *           description="ключ - локаль, значение - название значения харак. для этой локали"
 *     ),
 *     @OA\Property(property="en", type="string", example="winter wheat",
 *          description="ключ - локаль, значение - название значения харак. для этой локали"
 *     ),
 * )
 */

class CustomFeatureValueResource
{
    private $list = [];

    public function fill($data, $forSelect = false, $forMobile = false)
    {

        if($forSelect){
            foreach ($data as $key => $item){
                if($item->current){
                    $this->list[$item->current->value_id] = $item->current->name;
                }
            }

        } elseif ($forMobile){
            foreach ($data as $key => $item){
                if($item->current){
                    $this->list[$key]['id'] = $item->current->value_id;
                    $this->list[$key]['name'] = $item->current->name;
                }
            }
        } else {

            if($data instanceof FeatureValue){
                $this->list[0]['id'] = $data->id;
                /** @var $translates FeatureValueTranslates */
                foreach ($data->translates as $translates){
                    $this->list[0][$translates->lang] = $translates->name;
                }
            } else {
                foreach ($data as $key => $item){
                    $this->list[$key]['id'] = $item->id;
                    /** @var $translates FeatureValueTranslates */
                    foreach ($item->translates as $translates){
                        $this->list[$key][$translates->lang] = $translates->name;
                    }
                }
            }
        }

        return $this->list;
    }
}

