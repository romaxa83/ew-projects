<?php

namespace Tests\Unit\Commands\Commercial;

use App\Console\Commands\Commercial\CommercialProjectCheckStatusCommand;
use App\Models\Commercial\CommercialProject;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommercialProjectCheckStatusCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_check_projects_status(): void
    {
        $expired = CommercialProject::factory()
            ->requestIsExpired()
            ->create();

        $created = CommercialProject::factory()
            ->statusCreated()
            ->for($expired, 'previous')
            ->create();

        self::assertTrue($expired->status->isPending());
        self::assertTrue($created->status->isCreated());

        $this->artisan(CommercialProjectCheckStatusCommand::class);

        self::assertTrue($expired->fresh()->status->isCreated());
        self::assertTrue($created->fresh()->status->isPending());
    }
}
