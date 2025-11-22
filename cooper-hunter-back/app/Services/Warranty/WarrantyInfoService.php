<?php

namespace App\Services\Warranty;

use App\Dto\Warranty\WarrantyInfo\WarrantyInfoDto;
use App\Dto\Warranty\WarrantyInfo\WarrantyInfoPackageDto;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackageTranslation;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class WarrantyInfoService
{
    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function createOrUpdate(WarrantyInfoDto $dto): WarrantyInfo
    {
        $warranty = WarrantyInfo::firstOrNew();

        $this->fill($warranty, $dto);
        $warranty->save();

        $this->storeOrUpdateTranslations($warranty, $dto);

        $this->storePdf($warranty, $dto);
        $this->storePackages($warranty, $dto);

        return $warranty;
    }

    protected function fill(WarrantyInfo $info, WarrantyInfoDto $dto): void
    {
        $info->video_link = $dto->getVideoLink();
    }

    protected function storeOrUpdateTranslations(WarrantyInfo $warranty, WarrantyInfoDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $warranty->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'notice' => $translation->getNotice(),
                    'description' => $translation->getDescription(),
                    'seo_title' => $translation->getSeoTitle(),
                    'seo_description' => $translation->getSeoDescription(),
                    'seo_h1' => $translation->getSeoH1(),
                ]
            );
        }
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function storePdf(WarrantyInfo $warranty, WarrantyInfoDto $dto): void
    {
        if ($pdf = $dto->getPdf()) {
            $warranty
                ->addMedia($pdf)
                ->toMediaCollection(WarrantyInfo::MEDIA_COLLECTION_NAME);
        }
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function storePackages(WarrantyInfo $warranty, WarrantyInfoDto $dto): void
    {
        foreach ($dto->getPackagesDto() as $key => $packageDto) {
            if ($package = $warranty->packages->get($key)) {
                $this->updatePackage($package, $packageDto);
            } else {
                $this->storePackage($warranty, $packageDto);
            }
        }

        $warranty->refresh();
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    protected function updatePackage(WarrantyInfoPackage $package, WarrantyInfoPackageDto $dto): void
    {
        $this->updatePackageImage($dto, $package);

        foreach ($dto->getTranslations() ?? [] as $translation) {
            /** @var WarrantyInfoPackageTranslation $t */
            $t = $package->translations->where('language', $translation->getLanguage())->first();
            $t->title = $translation->getTitle();
            $t->description = $translation->getDescription();
            $t->save();
        }
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function updatePackageImage(WarrantyInfoPackageDto $dto, WarrantyInfoPackage $package): void
    {
        if ($image = $dto->getImage()) {
            $package
                ->addMedia($image)
                ->toMediaCollection(WarrantyInfoPackage::MEDIA_COLLECTION_NAME);
        }
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function storePackage(WarrantyInfo $warranty, WarrantyInfoPackageDto $dto): void
    {
        $package = new WarrantyInfoPackage();
        $package->warranty_info_id = $warranty->id;
        $package->sort = 0;
        $package->save();

        $this->updatePackageImage($dto, $package);

        foreach ($dto->getTranslations() as $translation) {
            $t = new WarrantyInfoPackageTranslation();
            $t->row_id = $package->id;
            $t->language = $translation->getLanguage();
            $t->title = $translation->getTitle();
            $t->description = $translation->getDescription();
            $t->save();
        }
    }
}
