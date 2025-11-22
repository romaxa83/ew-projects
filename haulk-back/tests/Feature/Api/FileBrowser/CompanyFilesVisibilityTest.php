<?php


namespace Api\FileBrowser;


use App\Models\Users\User;
use App\Services\FileBrowser\Actions\Files;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CompanyFilesVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_company_files_separated(): void
    {
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);
        $superadmin1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $superadmin1->assignRole(User::SUPERADMIN_ROLE);

        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);
        $superadmin2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $superadmin2->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($superadmin1);

        $fileName = 'file1.jpg';
        $file = UploadedFile::fake()->image($fileName);

        $this->postJson(
            route('filebrowser.upload'),
            [
                'source' => 'default',
                'files' => [
                    $file,
                ],
            ]
        )
            ->assertOk();

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => Files::ACTION,
                'source' => 'default',
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.sources.default.files.0.file', 'file1.jpg')
            ->assertJsonCount(1, 'data.sources.default.files');

        $this->loginAsCarrierSuperAdmin($superadmin2);

        $fileName = 'file2.jpg';
        $file = UploadedFile::fake()->image($fileName);

        $this->postJson(
            route('filebrowser.upload'),
            [
                'source' => 'default',
                'files' => [
                    $file,
                ],
            ]
        )
            ->assertOk();

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => Files::ACTION,
                'source' => 'default',
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.sources.default.files.0.file', 'file2.jpg')
            ->assertJsonCount(1, 'data.sources.default.files');
    }
}
