<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Wezom\Core\Exceptions\Factory\TranslationFactoryNotFoundException;

/**
 * @mixin Factory
 */
trait HasTranslationFactoryTrait
{
    public function configure(): self
    {
        return $this->configureTranslations();
    }

    protected function configureTranslations(): static
    {
        return $this->afterCreating(
            function (Model $model) {
                foreach (languages() as $language) {
                    $this->resolveTranslationFactory()
                        ->for($model, 'row')
                        ->create(['language' => $language->slug]);
                }
            }
        );
    }

    public function resolveTranslationFactory(): Factory
    {
        $factory = $this->guessTranslationModelFactoryName();

        if (class_exists($factory) && is_a($factory, Factory::class, true)) {
            return $factory::new();
        }

        throw new TranslationFactoryNotFoundException("Factory [$factory] does not exist.");
    }

    public function guessTranslationModelFactoryName(): string
    {
        return Str::of(static::class)
            ->replaceLast('Factory', 'TranslationFactory')
            ->value();
    }
}
