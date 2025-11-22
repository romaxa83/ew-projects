<?php

namespace Database\Factories\Clients;

use App\Enums\Clients\BanReasonsEnum;
use App\Models\Clients\Client;
use App\Models\Managers\Manager;
use App\Traits\Factory\HasPhonesFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Client[]|Client create(array $attributes = [])
 */
class ClientFactory extends Factory
{
    use HasPhonesFactory;

    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique->colorName,
            'contact_person' => $this->faker->unique->firstName . ' ' . $this->faker->unique->lastName,
            'manager_id' => Manager::factory(),
            'edrpou' => (string)mt_rand(10000000, 99999999),
            'is_moderated' => true,
            'active' => true,
        ];
    }

    public function withBan(): self
    {
        return $this->state(
            [
                'ban_reason' => BanReasonsEnum::NON_PAYMENT(),
                'show_ban_in_inspection' => true,
            ]
        );
    }

    public function disabled(): self
    {
        return $this->state(
            [
                'active' => false,
            ]
        );
    }
}
