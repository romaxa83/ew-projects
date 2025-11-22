<?php

namespace Tests\Feature\Mutations\BackOffice\Members;

use App\GraphQL\Mutations\BackOffice\Member\VerifyEmailMutation;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VerifyEmailMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = VerifyEmailMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function verify_by_tech(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $member Technician */
        $member = Technician::factory()->create([
            'email_verified_at' => null
        ]);

        $this->assertFalse($member->isEmailVerified());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($member->id, $member::MORPH_NAME)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $member->refresh();
        $this->assertTrue($member->isEmailVerified());
    }

    /** @test */
    public function verify_by_dealer(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $member Dealer */
        $member = Dealer::factory()->create([
            'email_verified_at' => null
        ]);

        $this->assertFalse($member->isEmailVerified());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($member->id, $member::MORPH_NAME)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $member->refresh();
        $this->assertTrue($member->isEmailVerified());
    }

    /** @test */
    public function verify_by_user(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $member User */
        $member = User::factory()->create([
            'email_verified_at' => null
        ]);

        $this->assertFalse($member->isEmailVerified());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($member->id, $member::MORPH_NAME)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $member->refresh();
        $this->assertTrue($member->isEmailVerified());
    }

    protected function getQueryStr($id, $member): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                    member: %s
                )
            }',
            self::MUTATION,
            $id,
            $member
        );
    }
}
