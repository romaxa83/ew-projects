<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Media;

use BackedEnum;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnitEnum;
use Wezom\Core\Contracts\ImageConversionInterface;
use Wezom\Core\Dto\FileDto;
use Wezom\Core\Media\Collections\MediaCollection;
use Wezom\Core\Media\ImageConversion;
use Wezom\Core\Models\Media;

/**
 * @see InteractsWithMedia::media()
 *
 * @property MediaCollection|Media[] $media
 */
trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia {
        media as baseMedia;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addMediaWithRandomName(
        $fileData,
        $collectionName,
        bool $clearCollection = false,
        bool $preservingOriginal = false,
        ?array $metaData = null
    ): void {
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

        if (!empty($metaData)) {
            $mediaItem = $mediaItem->withCustomProperties($metaData);
        }

        if ($preservingOriginal) {
            $mediaItem = $mediaItem->preservingOriginal();
        }

        $mediaItem->toMediaCollection($collectionName);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addMediaWithName(
        FileDto $dto,
        $collectionName,
    ): Media {
        $fileData = $dto->getFile();

        $mediaItem = $this->addMedia($fileData)
            ->setFileName(
                media_hash_file(
                    $fileData->getClientOriginalName(),
                    $fileData->getClientOriginalExtension()
                )
            )
            ->setName($dto->getName())
            ->withCustomProperties(['note' => $dto->getNote()]);
        /** @var Media $media */
        $media = $mediaItem->toMediaCollection($collectionName);

        return $media;
    }

    /** @return MorphMany<Media, $this> */
    public function media(): MorphMany
    {
        /** @var MorphMany<Media, $this> */
        return $this->baseMedia();
    }

    /** @return MorphMany<Media, $this> */
    public function files(): MorphMany
    {
        return $this->media();
    }

    /**
     * Add a file to the media library with a specific collection name.
     */
    public function addMediaToCollection(
        null|string|UploadedFile $file,
        string|BackedEnum|UnitEnum $collectionName = 'default',
        string $diskName = ''
    ): ?Media {
        if ($file === null) {
            return null;
        }

        /** @var Media|null */
        return app(FileAdderFactory::class)->create($this, $file)
            ->toMediaCollection(enum_to_string($collectionName), $diskName);
    }

    /** @return MorphOne<Media, $this> */
    public function singleMedia(): MorphOne
    {
        /** @var class-string<Media> $mediaModel */
        $mediaModel = config('media-library.media_model');

        return $this->morphOne($mediaModel, 'model');
    }

    /** @return MorphOne<Media, $this> */
    public function singleMediaByCollectionName(string|BackedEnum|UnitEnum $collectionName): MorphOne
    {
        /** @var MorphOne<Media, $this> */
        return $this->singleMedia()->where('collection_name', enum_to_string($collectionName));
    }

    /**
     * @throws Exception
     */
    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        // Skip media conversions for svg format
        if ($media && str_contains(strtolower($media->mime_type), 'svg')) {
            return;
        }

        $imageConversion = $this->resolveImageConversions();
        if (!$imageConversion) {
            return;
        }

        $conversions = $imageConversion->register()->getConversions();

        foreach ($conversions as $conversionOperation) {
            /** @var ImageConversion $conversionOperation */
            $conversion = $this->addMediaConversion($conversionOperation->getSize()->value)
                ->queued();

            $operations = $conversionOperation->getOperations();
            foreach ($operations as $operation => $arguments) {
                if ($operation == 'format') {
                    continue;
                }

                call_user_func_array([$conversion, $operation], $arguments);
            }

            $format = array_get($operations, 'format.format');
            if ($format == 'webp') {
                $conversion->format($format);
            } else {
                $conversion->keepOriginalImageFormat();
            }

            $conversion->performOnCollections(...$conversionOperation->getCollections());
        }
    }

    private function resolveImageConversions(): ?ImageConversionInterface
    {
        /** @phpstan-ignore-next-line */
        $class = static::getImageConversionsClass();

        if (!class_exists($class)) {
            return null;
        }

        return new $class();
    }

    private function getImageConversionsClass(): string
    {
        return method_exists($this, 'imageConversions')
            ? $this->imageConversions()
            : $this->resolveImageConversionsName();
    }

    private function resolveImageConversionsName(): string
    {
        return str_replace('\\Models\\', '\\Image\\Conversions\\', get_called_class() . 'ImageConversions');
    }
}
