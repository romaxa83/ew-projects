<?php


namespace Tests\Feature\Mutations\BackOffice\Users;


use App\GraphQL\Mutations\BackOffice\Users\UsersImportMutation;
use App\Models\Branches\Branch;
use App\Models\Users\User;
use App\Notifications\Users\SendPasswordNotification;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class UsersImportMutationTest
 * @package Tests\Feature\Mutations\BackOffice\Users
 *
 * Tests xlsx files has branches with ID 1,2 and 3. So you need to create these three branches by factory
 */
class UsersImportMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        Notification::fake();

        $this->loginAsAdminWithRole();

        Branch::factory()
            ->create(['id' => 1]);
        Branch::factory()
            ->create(['id' => 2]);
        Branch::factory()
            ->create(['id' => 3]);
    }

    public function test_import_users(): void
    {
        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(UsersImportMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()
                            ->createWithContent(
                                'import.xlsx',
                                file_get_contents(
                                    base_path('tests/files/users/correct.xlsx')
                                )
                            )
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UsersImportMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseCount(User::class, 5);

        Notification::assertTimesSent(5, SendPasswordNotification::class);
    }

    public function test_import_four_users(): void
    {
        User::factory()
            ->has(
                \App\Models\Phones\Phone::factory(['phone' => new Phone('380923901326')]),
                'phones'
            )
            ->create();

        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(UsersImportMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()
                            ->createWithContent(
                                'import.xlsx',
                                file_get_contents(
                                    base_path('tests/files/users/correct.xlsx')
                                )
                            )
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UsersImportMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseCount(User::class, 6);

        Notification::assertTimesSent(5, SendPasswordNotification::class);
    }

    public function test_import_incorrect_file(): void
    {
        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(UsersImportMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()
                            ->createWithContent(
                                'import.xlsx',
                                file_get_contents(
                                    base_path('tests/files/users/incorrect.xlsx')
                                )
                            )
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.utilities.import_file_incorrect')
                        ]
                    ]
                ]
            );

        $this->assertDatabaseCount(User::class, 0);

        Notification::assertTimesSent(0, SendPasswordNotification::class);
    }
}
