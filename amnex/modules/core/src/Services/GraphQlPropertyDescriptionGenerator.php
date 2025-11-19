<?php

namespace Wezom\Core\Services;

use GraphQL\Type\Definition\Deprecated;
use GraphQL\Type\Definition\Description;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Schema\Types\Scalars\Upload;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Support\DataProperty;
use Wezom\Core\Annotations\ImageConversions;
use Wezom\Core\Media\ImageConversion;
use Wezom\Core\Rules\ImageRulesAttribute;

readonly class GraphQlPropertyDescriptionGenerator
{
    private bool $isUpload;

    public function __construct(private DataProperty $property, Type $type, private array $rules = [])
    {
        $this->isUpload = Type::getNamedType($type) instanceof Upload;
    }

    public function getDescription(): ?string
    {
        return collect([
            $this->getDeprecatedDescription(),
            $this->getPropertyAttribute(Description::class)?->description,
            $this->getUploadDescription(),
        ])->filter()->join(PHP_EOL . PHP_EOL);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @return T|null
     */
    private function getPropertyAttribute(string $class): mixed
    {
        $result = $this->property->attributes
            ->filter(fn ($attribute) => is_a($attribute, $class, true))
            ->first();
        if ($result) {
            return $result;
        }

        foreach ($this->rules as $rule) {
            if (is_a($rule, $class)) {
                return $rule;
            }
        }

        return null;
    }

    private function getUploadDescription(): ?string
    {
        if (!$this->isUpload) {
            return null;
        }

        $result = collect();
        $result->add($this->getMimesDescription());
        $result->add($this->getMaxDescription());

        if ($this->hasPropertyAttribute(ImageConversions::class)) {
            $result->add($this->getImageConversionSizes());
        }

        return $result->filter()->join(PHP_EOL);
    }

    private function hasPropertyAttribute(string $class): bool
    {
        $result = $this->property->attributes->filter(fn ($attribute) => is_a($attribute, $class, true))->isNotEmpty();
        if ($result) {
            return true;
        }

        foreach ($this->rules as $rule) {
            if (is_a($rule, $class)) {
                return true;
            }
        }

        return false;
    }

    private function getMimesDescription(): ?string
    {
        if ($this->hasPropertyAttribute(Mimes::class)) {
            return $this->formatMimesDescription(
                $this->getPropertyAttribute(Mimes::class)
            );
        }

        if ($this->hasPropertyAttribute(ImageRulesAttribute::class)) {
            return $this->formatMimesDescription(
                $this->getPropertyAttribute(ImageRulesAttribute::class)->getMimesAttribute()
            );
        }

        return null;
    }

    private function formatMimesDescription(Mimes $mimesAttribute): string
    {
        return 'Formats: ' . implode(', ', $mimesAttribute->parameters()[0]);
    }

    private function getMaxDescription(): ?string
    {
        if ($this->hasPropertyAttribute(Max::class)) {
            return $this->formatMaxSizeDescription(
                $this->getPropertyAttribute(Max::class)
            );
        }

        if ($this->hasPropertyAttribute(ImageRulesAttribute::class)) {
            return $this->formatMaxSizeDescription(
                $this->getPropertyAttribute(ImageRulesAttribute::class)->getMaxSizeAttribute()
            );
        }

        return null;
    }

    private function formatMaxSizeDescription(Max $maxAttribute): string
    {
        return 'Max size: ' . $maxAttribute->parameters()[0] . 'Kb';
    }

    private function getImageConversionSizes(): ?string
    {
        $attribute = $this->getPropertyAttribute(ImageConversions::class);
        if (!$attribute) {
            return null;
        }
        $conversionsClass = $attribute->conversions;

        $currentConversions = collect((new $conversionsClass())->register()->getConversions())
            ->when(enum_to_string($attribute->collection), function (Collection $items, mixed $collection) {
                return $items->filter(fn (ImageConversion $c) => in_array($collection, $c->getCollections()));
            })
            ->filter(fn (ImageConversion $item) => $item->getSize()->is2xSize() || !$item->getSize()->isWebP())
            ->mapWithKeys(function (ImageConversion $conversion) {
                foreach ($conversion->getOperations() as $operationName => $args) {
                    if (
                        in_array(
                            $operationName,
                            ['manualCrop', 'resize', 'crop', 'fit', 'focalCrop', 'width', 'height']
                        )
                    ) {
                        return [
                            $conversion->getSize()->value => [
                                'width' => $args['width'] ?? 0,
                                'height' => $args['height'] ?? 0,
                            ],
                        ];
                    }
                }

                return null;
            })
            ->filter();

        if ($currentConversions->isEmpty()) {
            return null;
        }

        $result = ['Image sizes:'];
        foreach ($currentConversions as $sizeName => $sizes) {
            $result[] = sprintf(
                '%s: %sx%s px',
                $sizeName,
                $sizes['width'] ?: '?',
                $sizes['height'] ?: '?'
            );
        }

        return implode(PHP_EOL, $result);
    }

    private function getDeprecatedDescription(): ?string
    {
        $reason = $this->getPropertyAttribute(Deprecated::class)?->reason;

        return $reason ? 'DEPRECATED: ' . $reason : null;
    }
}
