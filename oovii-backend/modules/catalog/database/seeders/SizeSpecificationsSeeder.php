<?php

namespace WezomCms\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use LaravelLocalization;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecificationTranslation;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Catalog\Models\Specifications\SpecValueTranslation;

class SizeSpecificationsSeeder extends Seeder
{
    private array $values = [
        'XS',
        'S',
        'M',
        'L',
        'XL',
        'XXL',
        '42',
        '44',
        '46',
        '48',
        '50',
        '52',
        '54',
    ];

    public function run(): void
    {
        $size = Specification::query()->where('type', Specification::SIZE)->exists();

        if (!$size) {
            $size = $this->createSize();

            foreach ($this->values as $index => $value) {
                $this->createValue($size, $value, $index);
            }
        }
    }

    public function createSize(): Specification
    {
        $size = new Specification();
        $size->type = Specification::SIZE;
        $size->published = true;
        $size->slug = 'size';
        $size->save();

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
            $translation = new SpecificationTranslation();
            $translation->specification_id = $size->id;
            $translation->locale = $lang;
            $translation->name = 'Размер';
            $translation->save();
        }

        return $size;
    }

    public function createValue(Specification $spec, string $value, int $sort): SpecValue
    {
        $val = new SpecValue();
        $val->published = true;
        $val->specification_id = $spec->id;
        $val->slug = Str::slug($value);
        $val->sort = $sort;
        $val->save();

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
            $translation = new SpecValueTranslation();
            $translation->spec_value_id = $val->id;
            $translation->locale = $lang;
            $translation->name = $value;
            $translation->save();
        }

        return $val;
    }
}
