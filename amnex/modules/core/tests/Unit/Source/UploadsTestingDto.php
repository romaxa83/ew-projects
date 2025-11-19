<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Data;
use Wezom\Core\Annotations\ImageConversions;
use Wezom\Core\Enums\Images\ImageExtension;
use Wezom\Core\Rules\ImageRulesAttribute;

class UploadsTestingDto extends Data
{
    public function __construct(
        #[ImageRulesAttribute([ImageExtension::JPG, ImageExtension::PNG], 2048)]
        #[ImageConversions(TestingImageConversions::class, 'collection1')]
        public UploadedFile $oneFile,
        #[ImageRulesAttribute]
        #[ImageConversions(TestingImageConversions::class, 'collection2')]
        /** @var Collection<UploadedFile> */
        public Collection $filesCollection,
        #[File]
        #[Mimes(['pdf', 'doc', 'docx'])]
        #[Max(4096)]
        public UploadedFile $simpleFile
    ) {
    }
}
