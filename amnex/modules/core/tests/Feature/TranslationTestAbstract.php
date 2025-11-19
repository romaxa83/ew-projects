<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature;

use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\TranslationSideEnum;

abstract class TranslationTestAbstract extends TestCase
{
    protected function attrs(): array
    {
        return [
            'key' => 'validation.first_name',
            'language' => config('app.locale'),
            'text' => 'first_name',
            'side' => TranslationSideEnum::ADMIN(),
        ];
    }
}
