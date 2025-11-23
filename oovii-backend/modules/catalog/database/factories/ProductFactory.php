<?php

namespace WezomCms\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use LaravelLocalization;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $adminRepository = app(AdminRepository::class);

        $admin = Administrator::query()->select('id')
            ->where('super_admin', true)->first();

        $providers = array_values(array_flip($adminRepository->getByRoleForSelect(Role::DEFAULT_PROVIDER)));

        $providers[] = $admin->id;

        $name = $this->faker->words($this->faker->numberBetween(2, 5), true);

        $dimensions = Product::sortDimensions([
            $this->faker->numberBetween(5, 50),
            $this->faker->numberBetween(5, 50),
            $this->faker->numberBetween(5, 50),
        ]);

        $data = [
            'moderated' => true,
            'published' => true,
            'provider_id' => array_rand(array_flip($providers)),
            'cost' => $this->faker->numberBetween(0, 999),
            'cost_discount' => $this->faker->numberBetween(0, 999),
            'amount' => $this->faker->numberBetween(5, 99),
            'amount_one_user' => $this->faker->numberBetween(5, 20),
            'published_at' => Carbon::now()->addHours($this->faker->numberBetween(1, 20)),
            'weight' => $this->faker->numberBetween(1, 75) * 100,
            'dimensions' => $dimensions,
        ];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
            $langName = $name . ' - ' . $lang;

            $data[$lang] = [
                'name' => $langName,
                'description' => $this->faker->realText($this->faker->numberBetween(250, 500)),
                'feature_1' => $this->faker->sentence,
                'feature_2' => $this->faker->sentence,
                'feature_3' => $this->faker->sentence,
            ];
        }

        return $data;
    }
}
