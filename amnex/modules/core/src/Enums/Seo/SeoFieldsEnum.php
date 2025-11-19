<?php

declare(strict_types=1);

namespace Wezom\Core\Enums\Seo;

enum SeoFieldsEnum: string
{
    case SEO_H1 = 'seo_h1';
    case SEO_TITLE = 'seo_title';
    case SEO_DESCRIPTION = 'seo_description';
    case SEO_TEXT = 'seo_text';
    case SEO_IMAGE_TITLE = 'seo_image_title';
    case SEO_IMAGE_ALT = 'seo_image_alt';

    public static function getBaseSeo(): array
    {
        return [
            self::SEO_H1->value,
            self::SEO_TITLE->value,
            self::SEO_DESCRIPTION->value,
        ];
    }

    public static function getSeoWithText(): array
    {
        return [
            self::SEO_H1->value,
            self::SEO_TITLE->value,
            self::SEO_DESCRIPTION->value,
            self::SEO_TEXT->value,
        ];
    }

    public static function getFullSeo(): array
    {
        return [
            self::SEO_H1->value,
            self::SEO_TITLE->value,
            self::SEO_DESCRIPTION->value,
            self::SEO_TEXT->value,
            self::SEO_IMAGE_TITLE->value,
            self::SEO_IMAGE_ALT->value,
        ];
    }
}
