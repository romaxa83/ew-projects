<?php

namespace Database\Factories\Commercial;

use App\Models\Commercial\RDPAccount;
use App\Models\Technicians\Technician;
use Database\Factories\BaseFactory;
use Database\Factories\ForMemberTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|RDPAccount[]|RDPAccount create(array $attributes = [])
 */
class RDPAccountFactory extends BaseFactory
{
    use ForMemberTrait;

    protected $model = RDPAccount::class;

    public function definition(): array
    {
        return [
            'member_type' => Technician::MORPH_NAME,
            'member_id' => Technician::factory(),
            'login' => $this->faker->unique()->word,
            'password' => 'password',
            'active' => true,
            'start_date' => now(),
            'end_date' => now()->add(config('commercial.rdp.credentials.expiration_interval')),
        ];
    }

    public function expired(): self
    {
        return $this->state(
            [
                'end_date' => now()->subDay(),
            ]
        );
    }
}
