<?php

namespace App\Dto\Warranty\WarrantyInfo;

use Illuminate\Http\UploadedFile;

class WarrantyInfoDto
{
    private string $videoLink;
    private ?UploadedFile $pdf;

    /**
     * @var array<WarrantyInfoTranslationDto>
     */
    private array $translations;

    /**
     * @var array<WarrantyInfoPackageDto>
     */
    private array $packages;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->videoLink = $args['video_link'];
        $self->pdf = $args['pdf'] ?? null;

        foreach ($args['translations'] as $translation) {
            $self->translations[] = WarrantyInfoTranslationDto::byArgs($translation);
        }

        foreach ($args['packages'] as $package) {
            $self->packages[] = WarrantyInfoPackageDto::byArgs($package);
        }

        return $self;
    }

    public function getVideoLink(): string
    {
        return $this->videoLink;
    }

    public function getPdf(): ?UploadedFile
    {
        return $this->pdf;
    }

    /**
     * @return WarrantyInfoTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @return WarrantyInfoPackageDto[]
     */
    public function getPackagesDto(): array
    {
        return $this->packages;
    }
}
