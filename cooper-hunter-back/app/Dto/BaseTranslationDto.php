<?php


namespace App\Dto;


use Illuminate\Support\Str;

class BaseTranslationDto
{
    private array $translation;

    private ?string $title = null;
    private ?string $description = null;
    private ?string $slug = null;
    private string $language;

    public static function byTranslations(array $translations): array
    {
        $result = [];
        foreach ($translations as $translation) {
            $result[] = self::byTranslate($translation);
        }

        return $result;
    }

    public static function byTranslate(array $translation): static
    {
        $dto = new static();
        $dto->title = data_get($translation, 'title');
        $dto->description = data_get($translation, 'description');
        $dto->language = $translation['language'];

        if ($dto->title) {
            $dto->slug = Str::slug($dto->title);
        }

        $dto->translation = $translation;

        return $dto;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getSlug(): ?string
    {
        if ($this->title === null) {
            return null;
        }

        return $this->slug;
    }
}
