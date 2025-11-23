<?php

namespace WezomCms\Users\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class BonusHistoryFactory extends Factory
{
    protected $model = BonusHistory::class;

    public function definition(): array
    {
        return [
            'bonus' => $this->faker->numberBetween(100, 500),
            'bonus_count' => $this->faker->numberBetween(1, 5),
            'inviter_id' => User::factory(),
            'type' => BonusHistoryType::ACCRUAL,
        ];
    }
}
