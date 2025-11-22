<?php

namespace Tests\Feature\Mutations\BackOffice\Admins\Avatars;

use App\GraphQL\Mutations\BackOffice\Admins\Avatars\AvatarUploadMutation;
use App\GraphQL\Queries\BackOffice\Admins\AdminProfileQuery;
use App\Models\Admins\Admin;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;

class AvatarUploadMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AvatarUploadMutation::NAME;

    public function test_cannot_update_other_person_avatar(): void
    {
        Storage::fake();

        $this->loginAsAdmin();

        $admin = Admin::factory()->create();

        $query = $this->getQuery($admin);

        $this->assertServerError(
            $this->postGraphQlBackOfficeUpload($query),
            __('You cannot interact with other users\' avatars')
        );
    }

    protected function getQuery(Admin $admin): array
    {
        return GraphQLQuery::upload(self::MUTATION)
            ->args(
                [
                    'model_id' => $admin->getKey(),
                    'model_type' => new EnumValue($admin->getMorphClass()),
                    'image' => UploadedFile::fake()->image('avatar.png'),
                ]
            )
            ->select(
                [
                    'id',
                    'name',
                ]
            )
            ->make();
    }

    public function test_upload_avatar(): void
    {
        Storage::fake();

        $admin = $this->loginAsAdmin();

        $query = $this->getQuery($admin);

        $this->postGraphQlBackOfficeUpload($query)
            ->assertJsonPath('data.' . self::MUTATION . '.name', 'avatar');
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_get_profile_with_avatar(): void
    {
        Storage::fake();

        $admin = $this->loginAsAdmin();

        $admin->uploadAvatar(
            UploadedFile::fake()
                ->image('avatar.png')
        );

        $query = GraphQLQuery::query($profile = AdminProfileQuery::NAME)
            ->select(
                [
                    'avatar' => [
                        'id',
                        'name',
                    ],
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonPath('data.' . $profile . '.avatar.name', 'avatar');
    }
}
