<?php

namespace App\Foundations\Modules\Seo\Traits;

use App\Foundations\Modules\Seo\Models\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait SeoRequestRules
{
    public function seoRules(): array
    {
        return [
            'seo' => ['nullable', 'array'],
            'seo.h1' => ['nullable', 'string', 'max:200'],
            'seo.title' => ['nullable', 'string', 'max:200'],
            'seo.keywords' => ['nullable', 'string'],
            'seo.desc' => ['nullable', 'string'],
            'seo.text' => ['nullable', 'string'],
            'seo.image' => ['nullable', 'image',
                "max:" . byte_to_kb(config('media-library.max_file_size'))
            ],
        ];
    }
}
