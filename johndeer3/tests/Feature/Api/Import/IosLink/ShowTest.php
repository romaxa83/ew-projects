<?php

namespace Tests\Feature\Api\Import\IosLink;

use App\Helpers\DateFormat;
use App\Models\Import\IosLinkImport;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ShowTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $model = IosLinkImport::factory()->create(["user_id" => $user->id]);
        IosLinkImport::factory()->times(10)->create([
            "user_id" => $user->id
        ]);

        $this->getJson(route('admin.ios-links.import.show', [
            'iosLinkImport' => $model
        ]))
            ->assertJson($this->structureSuccessResponse([
                "id" => $model->id,
                "user_id" => $model->user_id,
                "user_name" => $user->full_name,
                "status" => $model->status,
                "file" => $model->file_link,
                "message" => $model->message,
                "created_at" => DateFormat::front($model->created_at),
                "updated_at" => DateFormat::front($model->updated_at),
                "error_data" => $model->error_data,
            ]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $model = IosLinkImport::factory()->create(["user_id" => $user->id]);

        $this->getJson(route('admin.ios-links.import.show', [
            'iosLinkImport' => $model
        ]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        $model = IosLinkImport::factory()->create(["user_id" => $user->id]);

        $this->getJson(route('admin.ios-links.import.show', [
            'iosLinkImport' => $model
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
