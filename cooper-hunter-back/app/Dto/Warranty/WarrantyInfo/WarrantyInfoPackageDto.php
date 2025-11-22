<?php

namespace App\Dto\Warranty\WarrantyInfo;

use Illuminate\Http\UploadedFile;

class WarrantyInfoPackageDto
{
    private ?UploadedFile $image;

    /**
     * @var array<WarrantyInfoPackageTranslationDto>
     */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->image = $args['image'] ?? null;

        foreach ($args['translations'] as $translation) {
            $self->translations[] = WarrantyInfoPackageTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    /**
     * @return WarrantyInfoPackageTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
