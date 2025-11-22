<?php


namespace App\Traits\Model;


use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;

    public function registerMediaConversions(Media $media = null): void
    {
        if (defined(static::class . '::CONVERSIONS')) {
            foreach (static::CONVERSIONS ?? [] as $convention => $size) {
                try {
                    $c = $this->addMediaConversion($convention);

                    if ($w = $size['width'] ?? null) {
                        $c->width($w);
                    }

                    if ($h = $size['height'] ?? null) {
                        $c->height($h);
                    }
                } catch (Throwable $e) {
                    logger($e);
                }
            }
        }
    }


    protected function mimePdf(): array
    {
        return [
            'application/pdf',
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
            'video/webp',
        ];
    }
}
