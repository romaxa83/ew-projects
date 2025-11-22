<?php


namespace Tests\Traits;


use App\Models\Localization\Language;
use Closure;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\WithFaker;

trait TranslationHelper
{
    use WithFaker;

    /**
     * @param string[] $fields
     * @param <Closure>[] $closures
     * @return array
     */
    protected function getTranslationsArray(array $fields = ['title', 'description'], array $closures = []): array
    {
        return languages()
            ->map(
                function (Language $language) use ($fields, $closures)
                {
                    $result = [
                        'language' => new EnumValue($language->slug)
                    ];

                    foreach ($fields as $field) {
                        if (array_key_exists($field, $closures)) {
                            $value = $closures[$field]();
                        } elseif (method_exists($this, $field . 'Translation')) {
                            $value = $this->{$field . 'Translation'}();
                        } else {
                            $value = $this->faker->text;
                        }
                        $result[$field] = $value;
                    }
                    return $result;
                }
            )
            ->values()
            ->toArray();
    }

    private function titleTranslation(): string
    {
        return $this->faker->text;
    }

    private function descriptionTranslation(): string
    {
        return $this->faker->text;
    }

    private function slugTranslation(): string
    {
        return $this->faker->slug;
    }
}
