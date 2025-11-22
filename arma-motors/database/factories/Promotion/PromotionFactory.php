<?php

namespace Database\Factories\Promotion;

use App\Models\Promotion\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = [Promotion::TYPE_INDIVIDUAL, Promotion::TYPE_COMMON];
        return [
            'type' => $type[random_int(0,1)],
            'sort' => 1,
            'active' => true,
            'link' => $this->faker->url,
            'start_at' => '1624350625',
            'finish_at' => '1624350625',
//            'start_at' => Carbon::now()->timestamp,
//            'finish_at' => Carbon::now()->add(10)->timestamp,
        ];
    }
}
