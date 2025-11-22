<?php

namespace Database\Seeders;

use App\Dto\Inventories\CategoryDto;
use App\Foundations\Modules\Localization\Dto\LanguageDto;
use App\Models\Inventories\Category;
use App\Services\Inventories\CategoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryCategorySeeder extends Seeder
{
    public function __construct(
        protected CategoryService $service
    )
    {}

    public function run(): void
    {
        if(!app()->environment('testing')){
            foreach ($this->data() as $item){
                if(!Category::query()->where('slug', Str::slug($item['name']))->exists()){
                    $this->service->create(
                        CategoryDto::byArgs([
                            'name' => $item['name'],
                            'slug' => Str::slug($item['name']),
                        ])
                    );
                }
            }
        }
    }

    protected function data(): array
    {
        return [
            ['name' => 'Truck parts',],
            ['name' => 'Trailer parts',],
        ];
    }
}


