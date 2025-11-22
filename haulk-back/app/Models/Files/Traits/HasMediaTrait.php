<?php

namespace App\Models\Files\Traits;

use App\Models\Files\ImageAbstract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Models\Media;

/**
 * @property Media[]|Collection media
 */
trait HasMediaTrait
{
    use \Spatie\MediaLibrary\HasMedia\HasMediaTrait;

    public function getFirstImage(): ?Media
    {
        return $this->getFirstMedia($this->getImageField());
    }

    public function getImageField(): string
    {
        return $this->getImageObject()->getField();
    }

    protected function getImageObject(): ImageAbstract
    {
        return resolve($this->getImageClass());
    }

    abstract public function getImageClass(): string;

    public function getMediaByWildcard(string $collectionWildcard): Collection
    {
        return $this->media->whereLike('collection_name', $collectionWildcard);
    }

    /**
     * @param UploadedFile $image
     * @return $this
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws DiskDoesNotExist
     */
    public function addImage(UploadedFile $image): self
    {
        $imageName = media_hash_file($image->getClientOriginalName(), $image->getClientOriginalExtension());
        $this->addMedia($image)
            ->setFileName($imageName)
            ->toMediaCollection($this->getImageField());

        return $this;
    }

    public function clearImageCollection(): self
    {
        $this->clearMediaCollection($this->getImageField());

        return $this;
    }

    /**
     * @param $collectionName
     * @param $fileData
     * @param false $clearCollection
     * @param bool $preservingOriginal
     * @param null $metaData
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addMediaWithRandomName(
        $collectionName,
        $fileData,
        bool $clearCollection = false,
        bool $preservingOriginal = false,
        $metaData = null
    ): void
    {
        if ($clearCollection) {
            $this->clearMediaCollection($collectionName);
        }

        $mediaItem = $this->addMedia($fileData)
            ->setFileName(
                media_hash_file(
                    $fileData->getClientOriginalName(),
                    $fileData->getClientOriginalExtension()
                )
            );

        if ($metaData && is_array($metaData)) {
            $mediaItem = $mediaItem->withCustomProperties($metaData);
        }

        if ($preservingOriginal) {
            $mediaItem = $mediaItem->preservingOriginal();
        }

        $mediaItem->toMediaCollection($collectionName);
    }

    /**
     * @param Media|null $media
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        foreach ($this->getImageObject()->conversions() as $size => $configuration) {
            $this->addConversion($size, $configuration);
        }
    }

    /**
     * @param string $name
     * @param array $configuration
     * @throws InvalidManipulation
     */
    protected function addConversion(string $name, array $configuration): void
    {
        $conversion = $this->addMediaConversion($name);

        if (isset($configuration['size'])) {
            $this->setSize($conversion, $configuration['size']);
        }

        if (isset($configuration['manipulations'])) {
            $this->setManipulations($conversion, $configuration['manipulations']);
        }

        if (isset($configuration['queued'])) {
            $this->setQueued($conversion, $configuration['queued']);
        }
    }

    /**
     * @param Conversion $conversion
     * @param array $size
     * @throws InvalidManipulation
     */
    protected function setSize(Conversion $conversion, array $size): void
    {
        if (isset($size['width'])) {
            $conversion->width($size['width']);
        }

        if (isset($size['height'])) {
            $conversion->height($size['height']);
        }
    }

    protected function setManipulations(Conversion $conversion, array $manipulations): void
    {
        foreach ($manipulations as $method => $params) {
            $conversion->$method(...$params);
        }
    }

    protected function setQueued(Conversion $conversion, $queued): Conversion
    {
        if ($queued) {
            return $conversion->queued();
        }

        return $conversion->nonQueued();
    }
}
