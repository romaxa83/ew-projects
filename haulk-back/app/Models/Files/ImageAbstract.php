<?php

namespace App\Models\Files;

abstract class ImageAbstract
{
    public const SIZE_ORIGINAL = 'original';
    public const SIZE_EXTRA_SMALL = 'xs';
    public const SIZE_SMALL = 'sm';
    public const SIZE_MEDIUM = 'md';
    public const SIZE_LARGE = 'lg';

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
}
