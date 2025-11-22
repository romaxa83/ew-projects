<?php

namespace App\Foundations\Modules\Media\Traits;

use App\Foundations\Modules\Media\Images\ImageAbstract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;

    public function media(): MorphMany
    {
        return $this->morphMany(config('media-library.media_model'), 'model')->orderBy('sort');
    }

    public function getFirstImage(string|null $collection = null): ?SpatieMedia
    {
        $mediaCollection = $collection ?? $this->getImageField();
        return $this->getFirstMedia($mediaCollection);
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

    public function addImage(UploadedFile $image, string|null $collection = null): self
    {
        $mediaCollection = $collection ?? $this->getImageField();
        $imageName = media_hash_file($image->getClientOriginalName(), $image->getClientOriginalExtension());

        $this->addMedia($image)
            ->setFileName($imageName)
            ->toMediaCollection($mediaCollection);

        return $this;
    }

    public function clearImageCollection(string|null $collection = null): self
    {
        $mediaCollection = $collection ?? $this->getImageField();
        $this->clearMediaCollection($mediaCollection);

        return $this;
    }

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
     * @param SpatieMedia|null $media
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(SpatieMedia $media = null): void
    {
        //original image in webp format:
        $this->addMediaConversion($this->getOriginalWebpConversionName())
            ->format(Manipulations::FORMAT_WEBP);
        $this->addMediaConversion($this->getOriginalJpgConversionName())
            ->format(Manipulations::FORMAT_JPG);

        $conversionsSetting = $this->getImageObject()->conversions();

        // перебиваем настройки по конверсии картинок, если для данной коллекции прописана отдельна конверсия
        if($media && isset($this->getImageObject()->conversionsSpecial()[$media->collection_name])){
            $conversionsSetting = $this->getImageObject()->conversionsSpecial()[$media->collection_name];
        }

        foreach ($conversionsSetting as $size => $configuration) {

            if(isset($configuration['formats'])){
                foreach ($configuration['formats'] as $format){
                    if($format == Manipulations::FORMAT_WEBP){
                        $this->addConversion($size, $configuration, $format);
                    } else {
                        $this->addConversion($size . '_' . $format, $configuration, $format);
                    }
                }
            } else {
                $this->addConversion($size, $configuration);
            }

            if(isset($configuration[ImageAbstract::X2])){
                foreach ($configuration[ImageAbstract::X2] as $ext){
                    $configuration['size']['width'] = $configuration['size']['width'] * 2;
                    $configuration['size']['height'] = $configuration['size']['height'] * 2;

                    if(isset($configuration['manipulations']['fit']['width'])){
                        $configuration['manipulations']['fit']['width'] = $configuration['manipulations']['fit']['width'] * 2;
                    }
                    if(isset($configuration['manipulations']['fit']['height'])){
                        $configuration['manipulations']['fit']['height'] = $configuration['manipulations']['fit']['height'] * 2;
                    }

                    $this->addConversion($size . '_'. ImageAbstract::X2 .'_'. $ext, $configuration, $ext);
                }
            }
        }
    }

    /**
     * @param string $name
     * @param array $configuration
     * @throws InvalidManipulation
     */
    protected function addConversion(string $name, array $configuration, string $format = null): void
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

        if($format){
            $this->setFormat($conversion, $format);
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
//        dd($manipulations);

        foreach ($manipulations as $method => $params) {
            $conversion->$method(...$params);
        }
    }

    protected function setFormat(Conversion $conversion, string $format): void
    {
        $conversion->format($format);
    }

    protected function setQueued(Conversion $conversion, $queued): Conversion
    {
        if ($queued) {
            return $conversion->queued();
        }

        return $conversion->nonQueued();
    }

    public static function mimeArchive(): array
    {
        return [
            'application/octet-stream',
            'application/x-rar-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
        ];
    }

    public function getMediaCollectionName(): string
    {
        return $this->resolveMediaCollectionName();
    }

    public function resolveMediaCollectionName(): string
    {
        if (defined(static::class . '::MEDIA_COLLECTION_NAME')) {
            return static::MEDIA_COLLECTION_NAME;
        }

        return 'default';
    }

    public function getMultiLangMediaCollectionName(string $lang): string
    {
        return $lang . '_' . $this->resolveMediaCollectionName();
    }

    public function getOriginalWebpConversionName(): string
    {
        return config('media-library.original_webp');
    }

    public function getOriginalJpgConversionName(): string
    {
        return config('media-library.original_jpg');
    }

    protected function mimePdf(): array
    {
        return [
            'application/pdf',
        ];
    }

    protected function mimeWord(): array
    {
        return [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }

    protected function mimeExcel(): array
    {
        return [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.ms-excel',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
        ];
    }

    protected function mimeImage(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/bmp',
            'image/gif',
            'image/svg+xml',
            'image/webp',
        ];
    }

    protected function mimeVideo(): array
    {
        return [
            'video/mp4',
            'video/webm',
            'video/webp',
        ];
    }
}
