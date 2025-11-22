<?php

namespace App\DTO\Page;

use App\Traits\AssetData;

class PageEditDTO
{
    use AssetData;

    private array $translations = [];

    private function __construct(array $data)
    {
        static::assetFieldAll($data, 'translations');

        foreach ($data['translations'] ?? [] as $translation){
            $this->translations[] = PageTranslationDTO::byArgs($translation);
        }
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        return $self;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}

