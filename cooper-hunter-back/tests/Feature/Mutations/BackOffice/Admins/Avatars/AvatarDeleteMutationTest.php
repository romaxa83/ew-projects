<?php

namespace Tests\Feature\Mutations\BackOffice\Admins\Avatars;

use App\GraphQL\Mutations\BackOffice\Admins\Avatars\AvatarDeleteMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class AvatarDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AvatarDeleteMutation::NAME;

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function test_delete_avatar(): void
    {
        Storage::fake();

        $admin = $this->loginAsAdmin();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'model_id' => $admin->getKey(),
                    'model_type' => new EnumValue($admin->getMorphClass()),
                ]
            )->make();

        $admin->uploadAvatar(
            UploadedFile::fake()
                ->image('avatar.png')
        );

        self::assertInstanceOf(Media::class, $admin->avatar());

        $this->postGraphQLBackOffice($query)
            ->assertJsonPath('data.' . self::MUTATION, true);

        self::assertNull($admin->fresh()->avatar());
    }
}
