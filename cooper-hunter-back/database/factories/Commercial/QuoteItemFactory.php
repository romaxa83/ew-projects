<?php

namespace Database\Factories\Commercial;

use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteItem;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|QuoteItem[]|QuoteItem create(array $attributes = [])
 */
class QuoteItemFactory extends BaseFactory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'price' => $this->faker->numberBetween(1, 1000),
            'qty' => $this->faker->numberBetween(1, 100),
            'commercial_quote_id' => CommercialQuote::factory(),
        ];
    }
}
