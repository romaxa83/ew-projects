<?php

namespace App\Console\Commands\FixDB;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use Illuminate\Console\Command;

class SetNewCategory extends Command
{
    protected $signature = 'fixdb:new-category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->page();
    }

    public function page(): void
    {
        if(!Category::query()->where('slug', 'spares')->first()){

            $position = Category::query()->latest('sort')->first();

            $model = new Category();
            $model->slug = 'spares';
            $model->active = true;
            $model->sort = $position->sort + 1;
            $model->main = true;
            $model->owner_type = ProductOwnerType::COOPER;
            $model->save();

            $t_en = new CategoryTranslation();
            $t_en->title = 'Spares';
            $t_en->description = 'Spares desc';
            $t_en->language = 'en';
            $t_en->row_id = $model->id;
            $t_en->save();

            $t_es = new CategoryTranslation();
            $t_es->title = 'Spares';
            $t_es->description = 'Spares desc';
            $t_es->language = 'es';
            $t_es->row_id = $model->id;
            $t_es->save();
        }
    }
}
