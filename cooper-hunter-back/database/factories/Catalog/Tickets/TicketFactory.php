<?php

namespace Database\Factories\Catalog\Tickets;

use App\Enums\Tickets\TicketStatusEnum;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Localization\Language;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Ticket[]|Ticket create(array $attributes = [])
 */
class TicketFactory extends BaseFactory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'serial_number' => ProductSerialNumber::factory()->create()->serial_number,
            'guid' => $this->faker->uuid,
            'code' => $this->faker->unique()->bothify,
            'status' => TicketStatusEnum::DONE,
            'case_id' => null,
            'order_parts' => [
                $this->faker->word,
                $this->faker->word,
                $this->faker->word,
            ]
        ];
    }

    public function byTechnician(): self
    {
        return $this->state(
            [
                'status' => TicketStatusEnum::NEW,
            ]
        );
    }

    public function configure(): TicketFactory
    {
        return $this->afterCreating(
            fn(Ticket $ticket) => $ticket
                ->translations()
                ->createMany(
                    languages()
                        ->map(
                            fn(Language $language) => [
                                'title' => $this->faker->text(30),
                                'description' => $this->faker->text,
                                'language' => $language->slug
                            ]
                        )
                        ->values()
                        ->toArray()
                )
        );
    }
}
