<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Services;

use Closure;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Spatie\LaravelData\Data;
use Wezom\Core\Traits\Model\HasTranslations;

trait InteractsWithTranslations
{
    protected function syncTranslations(Model $model, Data $dto, ?Closure $closure = null): void
    {
        if (!property_exists($dto, 'translations')) {
            throw new RuntimeException(sprintf('Missing "translations" property in %s', get_class($dto)));
        }

        /** @var Model&HasTranslations $model @phpstan-ignore-line */

        $dto->translations->each(static function (Data $translationDto) use ($model, $closure) {
            /** @phpstan-ignore-next-line */
            $translation = $model
                ->translations()
                ->updateOrCreate($translationDto->only('language')->all(), $translationDto->all());

            if ($closure !== null) {
                $closure($translation, $translationDto);
            }
        });
    }
}
