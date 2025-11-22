<?php

namespace Database\Factories\Commercial;

use App\Models\Admins\Admin;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteHistory;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|QuoteHistory[]|QuoteHistory create(array $attributes = [])
 */
class QuoteHistoryFactory extends BaseFactory
{
    protected $model = QuoteHistory::class;

    public function definition(): array
    {
        return [
            'position' => 1,
            'estimate' => $this->faker->city,
            'admin_id' => Admin::factory(),
            'data' => '{}',
            'quote_id' => CommercialQuote::factory(),
        ];
    }
}
