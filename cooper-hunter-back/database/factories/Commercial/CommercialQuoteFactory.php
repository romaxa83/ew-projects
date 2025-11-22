<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialQuote;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|CommercialQuote[]|CommercialQuote create(array $attributes = [])
 */
class CommercialQuoteFactory extends BaseFactory
{
    protected $model = CommercialQuote::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->safeEmail,
            'shipping_address' => $this->faker->address,
            'commercial_project_id' => CommercialProject::factory(),
            'status' => CommercialQuoteStatusEnum::PENDING,
        ];
    }
}

