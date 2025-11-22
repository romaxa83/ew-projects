<?php


namespace Tests\Feature\Mutations\BackOffice\Branches;


use App\GraphQL\Mutations\BackOffice\Branches\BranchesImportMutation;
use App\Models\Branches\Branch;
use App\Models\Locations\Region;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchesImportMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_import_branches(): void
    {
        Storage::fake();

        $this->loginAsAdminWithRole();

        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(BranchesImportMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()
                            ->createWithContent(
                                'import.xlsx',
                                file_get_contents(
                                    base_path('tests/files/branches/correct.xlsx')
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
                        BranchesImportMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseCount(Branch::class, 3);
    }

    public function test_import_two_branches(): void
    {
        Storage::fake();

        $this->loginAsAdminWithRole();

        Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'city' => 'Донецк',
                    'address' => 'ул. Улица',
                ]
            );

        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(BranchesImportMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()
                            ->createWithContent(
                                'import.xlsx',
                                file_get_contents(
                                    base_path('tests/files/branches/correct.xlsx')
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
                        BranchesImportMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseCount(Branch::class, 3);
    }

    public function test_import_incorrect_file(): void
    {
        Storage::fake();

        $this->loginAsAdminWithRole();

        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(BranchesImportMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()
                            ->createWithContent(
                                'import.xlsx',
                                file_get_contents(
                                    base_path('tests/files/branches/incorrect.xlsx')
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

        $this->assertDatabaseCount(Branch::class, 0);
    }
}
