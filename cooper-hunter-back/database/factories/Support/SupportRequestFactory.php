<?php

namespace Database\Factories\Support;

use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|SupportRequest[]|SupportRequest create(array $attributes = [])
 */
class SupportRequestFactory extends Factory
{
    protected $model = SupportRequest::class;

    public function definition(): array
    {
        return [
            'technician_id' => Technician::factory(),
            'is_closed' => false
        ];
    }

    public function closed(): self
    {
        return $this->state(
            [
                'is_closed' => true
            ]
        );
    }
}
