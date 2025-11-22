<?php

namespace Database\Seeders;

use App\Foundations\Modules\Localization\Dto\LanguageDto;
use App\Foundations\Modules\Localization\Models\Language;
use App\Foundations\Modules\Localization\Services\LanguageService;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function __construct(
        protected LanguageService $service
    )
    {}

    public function run(): void
    {
        foreach ($this->data() as $item){
            if(!Language::query()->where('slug', $item['slug'])->exists()){
                $this->service->create(
                    LanguageDto::byArgs($item)
                );
            }
        }
    }

    protected function data(): array
    {
        return [
            [
                'slug' => 'en',
                'name' => 'English',
                'native' => 'English',
                'default' => true,
                'sort' => 1,
            ],
            [
                'slug' => 'es',
                'name' => 'Spanish',
                'native' => 'Español',
                'default' => false,
                'sort' => 2,
            ],
            [
                'slug' => 'ru',
                'name' => 'Russian',
                'native' => 'Русский',
                'default' => false,
                'sort' => 3,
            ],
            [
                'slug' => 'uk',
                'name' => 'Ukrainian',
                'native' => 'Українська',
                'default' => false,
                'sort' => 4,
            ],
        ];
    }
}

