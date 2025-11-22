<?php

namespace Database\Factories\Catalog\Tickets;

use App\Models\Catalog\Tickets\Ticket;
use App\Models\Catalog\Tickets\TicketTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|TicketTranslation[]|TicketTranslation create(array $attributes = [])
 */
class TicketTranslationFactory extends BaseTranslationFactory
{
    protected $model = TicketTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Ticket::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
        ];
    }
}
