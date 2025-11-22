<?php

namespace App\DTO\Page;

use App\Traits\AssetData;

final class PageDTO
{
    use AssetData;

    private string $alias;
    private array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'alias');
        self::assetFieldAll($args, 'translations');

        $self = new self();

        $self->alias = $args['alias'];

        foreach ($args['translations'] ?? [] as $translation){
            $self->translations[] = PageTranslationDTO::byArgs($translation);
        }

        return $self;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}


