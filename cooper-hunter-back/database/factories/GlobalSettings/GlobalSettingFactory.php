<?php

namespace Database\Factories\GlobalSettings;

use App\Models\GlobalSettings\GlobalSetting;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|GlobalSetting[]|GlobalSetting create(array $attributes = [])
 */
class GlobalSettingFactory extends BaseFactory
{
    protected $model = GlobalSetting::class;

    public function definition(): array
    {
        return [
            'footer_address' => $this->faker->word,
            'footer_email' => $this->faker->word,
            'footer_phone' => $this->faker->word,
            'footer_instagram_link' => $this->faker->word,
            'footer_meta_link' => $this->faker->word,
            'footer_twitter_link' => $this->faker->word,
            'footer_youtube_link' => $this->faker->word,
            'footer_additional_email' => $this->faker->word,
            'footer_app_store_link' => $this->faker->word,
            'footer_google_pay_link' => $this->faker->word,
            'company_site' => $this->faker->word,
            'company_title' => $this->faker->word,
        ];
    }
}
