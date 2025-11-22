<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserUpdateMutation;
use App\Models\Auth\MemberPhoneVerification;
use App\Models\Users\User;
use App\Permissions\Users\UserUpdatePermission;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UserUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = UserUpdateMutation::NAME;

    public function test_unverified_user_can_update_profile(): void
    {
        Config::set('grants.filter_enabled', true);
        $user = User::factory()->notVerified()->create();

        $this->loginAsUserWithRole($user);

        $this->updateProfile($user);
    }

    public function test_user_can_update_profile(): void
    {
        $user = $this->loginAsUser()->assignRole(
            $this->generateRole('User update', [UserUpdatePermission::KEY])
        );

        $this->updateProfile($user);
    }

    public function test_user_can_update_phone_without_sms_access_token(): void
    {
        $user = $this->loginAsUser()->assignRole(
            $this->generateRole('User update', [UserUpdatePermission::KEY])
        );

        //update with same phone number
        $this->updateProfile($user);

        $user->refresh();

        self::assertNotNull($user->phone);
        self::assertNotNull($user->phone_verified_at);

        //update with different phone number
        $user->phone = new Phone('123456789');
        $this->updateProfile($user);

        $user->refresh();

        self::assertNotNull($user->phone);
        self::assertNull($user->phone_verified_at);
    }

    public function test_user_can_update_phone_with_sms_access_token(): void
    {
        $code = MemberPhoneVerification::factory()->withAccessToken()->create();

        $user = $this->loginAsUser()->assignRole(
            $this->generateRole('User update', [UserUpdatePermission::KEY])
        );

        $this->updateProfile($user, $code->access_token);

        $user->refresh();

        self::assertNotNull($user->phone);
        self::assertNotNull($user->phone_verified_at);
    }

    protected function updateProfile(User $user, string $smsAccessToken = ''): TestResponse
    {
        $query = sprintf(
            'mutation {
                %s (
                    first_name: "%s"
                    last_name: "%s"
                    email: "%s"
                    phone: "%s"
                    %s
                )
                {
                    id
                    first_name
                    last_name
                    email
                    phone
                    email_verified_at
                    lang
                }
            }',
            self::MUTATION,
            $newFirstName = 'new name',
            $newLastName = 'new last name',
            $user->email,
            $user->phone,
            $smsAccessToken ? 'sms_access_token: "' . $smsAccessToken . '"' : '',
        );

        return $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $user->id,
                            'first_name' => $newFirstName,
                            'last_name' => $newLastName,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'email_verified_at' => $user->email_verified_at,
                            'lang' => $user->lang,
                        ]
                    ]
                ]
            );
    }
}
