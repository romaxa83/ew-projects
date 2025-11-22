<?php

namespace App\Foundations\Modules\Media\Images;

abstract class ImageAbstract
{
    public const SIZE_ORIGINAL = 'original';
    public const SIZE_EXTRA_SMALL = 'xs';
    public const SIZE_SMALL = 'sm';
    public const SIZE_MEDIUM = 'md';
    public const SIZE_LARGE = 'lg';
    public const SIZE_EXTRA_LARGE = 'xl';

    public const X2 = '2x'; // for apple

    public function getField() :string
    {
        return 'image';
    }

    /**
     * @example [
     *  'xs' => [
     *      'size' => [
     *          'width' => 840,
     *          'height' => 480,
     *       ],
     *       'manipulations' => [
     *              'sharpen' => [10],
     *       ],
     *      'queued' => true, //queue is default
     * ]
     *
     * @return array
     */
    public function conversions(): array
    {
        return [];
    }

    // это конверсия отдельна от основной, но в рамках одной сущности
    // к примеру у сущности нужно грузить картинки (не такие как основные)
    // для мобильной версии и для них нужна другая конверсия,
    // пример заполнения можно увидеть тут - App\Foundations\Modules\Media\Images\CategoryImage
    public function conversionsSpecial(): array
    {
        return [];
    }
}

