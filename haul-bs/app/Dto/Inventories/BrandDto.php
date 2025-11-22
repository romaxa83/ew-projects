<?php

namespace App\Dto\Inventories;

use App\Foundations\Modules\Seo\Dto\SeoDto;

class BrandDto
{
    public string $name;
    public string $slug;
    public SeoDto $seoDto;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->slug = data_get($data, 'slug');

        $self->seoDto = SeoDto::byArgs($data['seo'] ?? []);

        return $self;
    }
}
