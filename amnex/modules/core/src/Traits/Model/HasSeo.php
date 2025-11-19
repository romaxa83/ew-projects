<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Wezom\Core\Enums\Seo\SeoFieldsEnum;

/**
 * @property bool $hasSeoTextField = false
 *
 * @mixin Model
 */
trait HasSeo
{
    private const string SEO_PREFIX = 'seo_';

    /**
     *  List of 'seo_' prefixed field names
     *
     * @var array<string>
     */
    protected array $seoFields = [];

    /**
     * GraphQL default accessor
     */
    public function getSeoAttribute(): array
    {
        return $this->getSeo();
    }

    /** @return array<string, string|null> */
    public function getSeo(): array
    {
        $seo = [];

        foreach ($this->getAllSeoFields() as $seoField) {
            $seo[$this->getSeoFieldName($seoField)] = $this->getAttribute($seoField);
        }

        return $seo;
    }

    private function getAllSeoFields(): array
    {
        $baseSeoFields = SeoFieldsEnum::getBaseSeo();

        if ($this->hasSeoTextField) {
            $baseSeoFields[] = SeoFieldsEnum::SEO_TEXT->value;
        }

        return array_merge($baseSeoFields, $this->seoFields);
    }

    private function getSeoFieldName(string $seoField): string
    {
        return Str::after($seoField, self::SEO_PREFIX);
    }
}
