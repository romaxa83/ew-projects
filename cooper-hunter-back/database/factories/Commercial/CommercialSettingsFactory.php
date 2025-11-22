<?php

namespace Database\Factories\Commercial;

use App\Models\Commercial\CommercialSettings;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|CommercialSettings[]|CommercialSettings create(array $attributes = [])
 */
class CommercialSettingsFactory extends BaseFactory
{
    protected $model = CommercialSettings::class;

    public function definition(): array
    {
        return [
            'nextcloud_link' => $this->faker->imageUrl,
            'quote_title' => $this->faker->title,
            'quote_address_line_1' => $this->faker->address,
            'quote_address_line_2' => $this->faker->address,
            'quote_phone' => $this->faker->phoneNumber,
            'quote_email' => $this->faker->safeEmail,
            'quote_site' => $this->faker->url,
        ];
    }
}
