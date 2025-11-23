<?php

namespace WezomCms\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecificationTranslation;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Catalog\Models\Specifications\SpecValueTranslation;

class ColorSpecificationsSeeder extends Seeder
{
    public $colors = [
        'green' => '#00FF00',
        'yellow' => '#FFFF00',
        'blue' => '#0000FF',
        'red' => '#FF0000',
        'black' => '#000000',
        'white' => '#FFFFFF',
        'pink' => '#FFC0CB',
        'grey' => '#808080',
        'violet' => '#8F00FF',
    ];

    public $colorName = [
        'green' => 'Зеленый',
        'yellow' => 'Желтый',
        'blue' => 'Синий',
        'red' => 'Красный',
        'black' => 'Черный',
        'white' => 'Белый',
        'pink' => 'Розовый',
        'grey' => 'Серый',
        'violet' => 'Фиолетовый',
    ];

    public function run()
    {
        $color = Specification::query()->where("type", Specification::COLOR)->first();
        if($color == null){
            $color = $this->createColor();
        }

        foreach ($this->colors as $key => $item){
            $val = SpecValue::query()->where('slug', $key)->first();
            if($val == null){
                $this->createValue($color, $key, $item);
            }
        }
    }

    public function createColor(): Specification
    {
        $color = new Specification();
        $color->type = Specification::COLOR;
        $color->name = 'Цвет';
        $color->published = true;
        $color->save();

//        foreach (array_keys(app('locales')) as $locale){
//        $t = new SpecificationTranslation();
//        $t->locale = 'ru';
//        $t->specification_id = $color->id;
//        $t->name = $color->name;
//        dd('d');
//        $t->save();

        $t_k = new SpecificationTranslation();
        $t_k->locale = 'kk';
        $t_k->specification_id = $color->id;
        $t_k->name = $color->name;
        $t_k->save();
//        }

        return $color;
    }

    public function createValue(Specification $spec, string $slug, string $value): SpecValue
    {
        $val = new SpecValue();
        $val->published = true;
        $val->specification_id = $spec->id;
        $val->slug = $slug;
        $val->color = $value;
        $val->save();

        foreach (array_keys(app('locales')) as $locale){
            $t = new SpecValueTranslation();
            $t->locale = $locale;
            $t->spec_value_id = $val->id;
            $t->name = $this->colorName[$slug];
            $t->save();
        }

        return $val;
    }
}
