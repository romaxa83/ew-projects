<?php

namespace Tests\Feature\Orders\Notes;

use App\Models\Communications\CommunicationRecord;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\NoteBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class RemoveTest extends TestCase
{
    use DatabaseTransactions;

    protected RoleBuilder $roleBuilder;
    protected UserBuilder $userBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected NoteBuilder $noteBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;

    public function setUp(): void
    {
        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->noteBuilder = resolve(NoteBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function remove_note_check_communication_record()
    {
        $user = $this->loginUser();
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $note Order\Notes */
        $note = $this->noteBuilder->user($user)->create();

        $rec = $this->communicationRecordBuilder
            ->entity($note)
            ->create();
        $recId = $rec->id;
        $noteId = $note->id;

        $this->post(route('orders.notes.remove'), [
            'id' => $note->id,
        ])
            ->assertJson([
                'success' => true
            ])
        ;

        $this->assertNull(Order\Notes::find($noteId));
        $this->assertTrue(Order\Notes::query()->withTrashed()->where('id',$noteId)->exists());
        $this->assertNull(CommunicationRecord::find($recId));
    }

    /** @test */
    public function fail_remove_not_user()
    {
        $this->loginUser();
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $note Order\Notes */
        $note = $this->noteBuilder->create();

        $noteId = $note->id;

        $this->post(route('orders.notes.remove'), [
            'id' => $note->id,
        ])
            ->assertJson([
                'success' => false
            ])
        ;

        $this->assertNotNull(Order\Notes::find($noteId));
    }
}
