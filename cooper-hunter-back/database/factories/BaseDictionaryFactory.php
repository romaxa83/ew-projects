<?php


namespace Database\Factories;


use App\Models\BaseHasTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

abstract class BaseDictionaryFactory extends Factory
{

    public function definition(): array
    {
        return [
            'active' => 1,
            'sort' => 1
        ];
    }

    public function configure(): BaseDictionaryFactory
    {
        return $this->afterCreating(
            fn(BaseHasTranslation $orderCategory) => languages()
                ->pluck('slug')
                ->each(
                    fn(string $language) => $orderCategory->translations()
                        ->create(
                            [
                                'slug' => $this->faker->unique->slug,
                                'title' => $this->faker->unique->word,
                                'description' => $this->faker->unique->text,
                                'language' => $language
                            ]
                        )
                        ->save()
                )
        );
    }

}
