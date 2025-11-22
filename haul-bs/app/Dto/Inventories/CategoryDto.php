<?php

namespace App\Dto\Inventories;

use App\Foundations\Modules\Seo\Dto\SeoDto;
use App\Models\Inventories\Category;
use Illuminate\Http\UploadedFile;

class CategoryDto
{
    public string $name;
    public string $slug;
    public string|null $desc;
    public int|string|null $parentId;
    public int $position;
    public bool $displayMenu;
    public UploadedFile|null $imageHeader;
    public UploadedFile|null $imageMenu;
    public UploadedFile|null $imageMobile;
    public SeoDto $seoDto;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->slug = data_get($data, 'slug');
        $self->desc = data_get($data, 'desc');
        $self->parentId = data_get($data, 'parent_id');
        $self->position = $data['position'] ?? 0;
        $self->displayMenu = $data['display_menu'] ?? false;
        $self->imageMenu = data_get($data, Category::IMAGE_MENU_FIELD_NAME);
        $self->imageHeader = data_get($data, Category::IMAGE_HEADER_FIELD_NAME);
        $self->imageMobile = data_get($data, Category::IMAGE_MOBILE_FIELD_NAME);

        $self->seoDto = SeoDto::byArgs($data['seo'] ?? []);

        return $self;
    }
}
