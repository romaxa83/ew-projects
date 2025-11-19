<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature\Mutations\Back;

use Wezom\Admins\Traits\AdminTestTrait;
use Wezom\Core\Models\Translation;
use Wezom\Core\Testing\Crud\DeleteTestAbstract;

class BackTranslationDeleteTest extends DeleteTestAbstract
{
    use AdminTestTrait;

    protected function model(): string
    {
        return Translation::class;
    }

    public function testCantDeleteByNotAuthAdmin(): void
    {
        $this->executeCantDeleteByNotAuthAdmin();
    }

    public function testCantDeleteByNotPermittedAdmin(): void
    {
        $this->executeCantDeleteByNotPermittedAdmin();
    }

    public function testDoSuccess(): void
    {
        $this->executeDoSuccess();
    }

    public function testDeleteNotExisting(): void
    {
        $this->executeDeleteNotExisting();
    }
}
