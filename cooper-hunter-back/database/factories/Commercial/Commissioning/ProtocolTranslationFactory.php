<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\ProtocolTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ProtocolTranslation[]|ProtocolTranslation create(array $attributes = [])
 */
class ProtocolTranslationFactory extends BaseTranslationFactory
{
    protected $model = ProtocolTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Protocol::factory(),
            'title' => $this->faker->sentence,
            'desc' => $this->faker->text,
            'language' => Language::factory(),
        ];
    }
}
