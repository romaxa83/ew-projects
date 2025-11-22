<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\InspectionReason;
use App\Models\Dictionaries\InspectionReasonTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method InspectionReason|InspectionReason[]|Collection create(array $attributes = [])
 */
class InspectionReasonFactory extends Factory
{
    protected $model = InspectionReason::class;

    public function definition(): array
    {
        return [
            'active' => true,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (InspectionReason $inspectionReason) {
                foreach (languages() as $language) {
                    InspectionReasonTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $inspectionReason->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
