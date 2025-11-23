<?php

namespace WezomCms\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecificationTranslation;
use WezomCms\Catalog\Repositories\SpecificationRepository;

class SpecificationsSeeder extends Seeder
{
    public function run()
    {
        app(ColorSpecificationsSeeder::class)->run();
        // $this->fill();
    }

    public function fill()
    {
        $repo = app(SpecificationRepository::class);

        DB::beginTransaction();
        try {
            foreach ($this->data() as $item){
                if($repo->existBy('slug', $item['slug'])){
                    break;
                }

                $model = new Specification();
                $model->published = true;
                $model->multiple = false;
                $model->slug = $item['slug'];
                $model->save();

                foreach ($item['translations'] as $locale => $one){
                    $t = new SpecificationTranslation();
                    $t->specification_id = $model->id;
                    $t->locale = $locale;
                    $t->name = $one['name'];
                    $t->save();
                }
            }

            DB::commit();
        } catch(\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }
    }

    public function data()
    {
        return [
            [
                "slug" => "razmer",
                "translations" => [
                    "ru" => ["name" => "Размер"],
                    "kk" => ["name" => "Размер"],
                ]
            ],
        ];
    }
}
