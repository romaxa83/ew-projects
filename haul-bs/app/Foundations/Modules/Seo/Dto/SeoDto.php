<?php

namespace App\Foundations\Modules\Seo\Dto;

use Illuminate\Http\UploadedFile;

class SeoDto
{
    public string|null $h1;
    public string|null $title;
    public string|null $keywords;
    public string|null $desc;
    public string|null $text;
    public UploadedFile|null $image;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->h1 = data_get($data, 'h1');
        $self->title = data_get($data, 'title');
        $self->keywords = data_get($data, 'keywords');
        $self->desc = data_get($data, 'desc');
        $self->text = data_get($data, 'text');
        $self->image = data_get($data, 'image');

        return $self;
    }
}
