<?php

namespace Database\Seeders;

use App\DTO\Catalog\Calc\SparesGroupDTO;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Services\Catalog\Calc\SparesGroupService;

class SparesGroupSeeder extends BaseSeeder
{

    public function __construct(protected SparesGroupService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('spares_groups')->truncate();
        \DB::table('spares_group_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = SparesGroupDTO::byArgs($item);
                    $this->service->create($dto);
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    protected function data(): array
    {
        return [
            [
                'sort' => 1,
                'type' => SparesGroup::TYPE_VOLUME,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Моторное масло',
                        'unit' => 'л.',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Моторне масло',
                        'unit' => 'л.',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'type' => SparesGroup::TYPE_QTY,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Фильтры',
                        'unit' => 'шт.',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Фильтры',
                        'unit' => 'шт.',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'type' => SparesGroup::TYPE_QTY,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Расходники',
                        'unit' => 'шт.',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Расходники',
                        'unit' => 'шт.',
                    ],
                ]
            ],
        ];
    }
}




