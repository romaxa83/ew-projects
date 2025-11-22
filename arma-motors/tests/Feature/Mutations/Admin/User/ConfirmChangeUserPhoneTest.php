<?php

namespace Tests\Feature\Mutations\Admin\User;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Notifications\Mail\CredentialsNotification;
use App\Services\Localizations\LocalizationService;
use App\Types\Permissions;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class ConfirmChangeUserPhoneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

//    /** @test */
//    public function change_success()
//    {
//        $builder = $this->adminBuilder();
//        $admin = $builder
//            ->createRoleWithPerm(Permissions::USER_MODERATION)
//            ->create();
//        $this->loginAsAdmin($admin);
//
//        $newPhone = '398777777777';
//        $user = $this->userBuilder()->phoneVerify()->setNewPhone($newPhone)->create();
//
//        $this->assertTrue($user->phone_verify);
//        $this->assertNotEquals($user->phone, $newPhone);
//        $this->assertEquals($user->new_phone, $newPhone);
//
//        $response = $this->graphQL($this->getQueryStr($user->id));
//dd($response);
//        $responseData = $response->json('data.adminConfirmEditUserPhone');
//
//        $this->assertArrayHasKey('message', $responseData);
//        $this->assertArrayHasKey('code', $responseData);
//        $this->assertArrayHasKey('status', $responseData);
//
//        $this->assertEquals(__('message.phone change'), $responseData['message']);
//        $this->assertTrue($responseData['status']);
//
//        $user->refresh();
//
//        $this->assertFalse($user->phone_verify);
//        $this->assertEquals($user->phone, $newPhone);
//        $this->assertNull($user->new_phone);
//    }
//
//    /** @test */
//    public function fail_not_new_phone()
//    {
//        $builder = $this->adminBuilder();
//        $admin = $builder
//            ->createRoleWithPerm(Permissions::USER_MODERATION)
//            ->create();
//        $this->loginAsAdmin($admin);
//
//        $newPhone = '398777777777';
//        $user = $this->userBuilder()->phoneVerify()->create();
//
//        $this->assertNull($user->new_phone, $newPhone);
//
//        $response = $this->graphQL($this->getQueryStr($user->id));
//
//        $this->assertArrayHasKey('errors', $response->json());
//        $this->assertEquals(__('error.user not have new phone'), $response->json('errors.0.message'));
//    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::USER_MODERATION)
            ->create();

        $newPhone = '398777777777';
        $user = $this->userBuilder()->phoneVerify()->setNewPhone($newPhone)->create();

        $response = $this->graphQL($this->getQueryStr($user->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::USER_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $newPhone = '398777777777';
        $user = $this->userBuilder()->phoneVerify()->setNewPhone($newPhone)->create();

        $response = $this->graphQL($this->getQueryStr($user->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                adminConfirmEditUserPhone(input: {id: %s}){
                    message
                    status
                    code
                }
            }',
        $id
        );
    }
}


