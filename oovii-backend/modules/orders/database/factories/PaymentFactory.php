<?php


namespace WezomCms\Orders\Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelLocalization;
use WezomCms\Orders\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $data = [
            'icon' => $this->faker->filePath(),
        ];

        $name = $this->faker->words(2, true);

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
            $data[$lang] = [
                'name' => $name . ' - ' . $lang,
            ];
        }

        return $data;
    }
}
