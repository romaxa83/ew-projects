<?php
namespace Database\Factories\History;

use App\Models\History\History;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryFactory extends Factory
{
    protected $model = History::class;

    public function definition(): array
    {
        return [
            'model_type' => null,
            'model_id' => null,
            'user_id' => null,
            'user_role' => null,
            'message' => $this->faker->bothify('#######'),
            'meta' => [],
            'performed_at' => CarbonImmutable::now(),
            'performed_timezone' => null,
            'histories' => [],
            'type' => 0,
        ];
    }
}
